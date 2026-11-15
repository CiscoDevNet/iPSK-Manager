<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/http-harness.php';
ob_start();
$unknownSchema=new iPSKManagerDatabase();
try{$unknownSchema->getLoginNotice();throw new LogicException('Unknown schema bypassed notice');}catch(RuntimeException $expected){}
$unknownSchema->dbSchemaVersion=13;verify(!$unknownSchema->getLoginNotice()['enabled'],'Pre-migration recovery login blocked');

foreach([[1,''],[1," \n\t"],[2,'Notice'],[[], 'Notice'],[1,[]],[1,str_repeat('x',20001)],[1,"Invalid\0notice"],[1,"\xff"]] as [$enabled,$text])verify(ipskLoginNoticeInput($enabled,$text)===false,'Malformed notice accepted');
verify(ipskLoginNoticeInput('1',"A\r\nB\rC")===['enabled'=>true,'text'=>"A\nB\nC"],'Line endings changed unexpectedly');
verify(ipskLoginNoticeInput(0,'')!==false && ipskLoginNoticeFields(['enabled'=>false])==='','Disabled behavior changed');
$notice=['enabled'=>true,'text'=>'</textarea><script>alert(1)</script>','revision'=>str_repeat('a',64)];
verify(!str_contains(ipskLoginNoticeFields($notice),'<script>'),'Notice HTML executed');
class Notice105Database {
    public $writes=[],$logs=[];
    function loginNoticesAvailable(){return true;}
    function getLoginNotice(){return ['enabled'=>true,'text'=>'Fixture','revision'=>str_repeat('a',64)];}
    function saveLoginNotice(...$args){$this->writes[]=$args;return true;}
    function addLogEntry(...$args){$this->logs[]=$args;}
}
$ipskISEDB=new Notice105Database();$_POST=['noticeEnabled'=>'1','noticeText'=>"One & two\n<script>literal</script>"];
$_SESSION=validSession()+['adminAuthorized'=>true,'logonSID'=>'admin-owner'];
include __DIR__.'/../supportfiles/adminportals/modules/sysconfig/loginnotice.inc.php';
verify($ipskISEDB->writes===[['1',$_POST['noticeText'],'admin-owner']],'Notice text changed before storage');
foreach([[],validSession()] as $session){$_SESSION=$session;include __DIR__.'/../supportfiles/adminportals/modules/sysconfig/loginnotice.inc.php';verify(count($ipskISEDB->writes)===1 && http_response_code()===403,'Non-admin changed shared notice');}
$_SESSION=validSession()+['logonSID'=>'owner'];ipskRecordLoginNoticeAcceptance($ipskISEDB,'portal-7',$notice);
verify(count($ipskISEDB->logs)===1 && str_contains($ipskISEDB->logs[0][0],'REVISION:'.str_repeat('a',64)) && str_contains($ipskISEDB->logs[0][0],'SID:owner') && !str_contains($ipskISEDB->logs[0][0],'<script>'),'Acceptance audit incorrect');
$_SESSION=validSession()+['adminAuthorized'=>true];$_POST=[];$_SERVER['REQUEST_METHOD']='POST';
verify(ipskLoginNoticeGate($ipskISEDB,'login.php','index.php',false)===false && !ipskAdminSessionCheck(),'Rejected acknowledgment kept previous identity');
withHttpFixture(function($base){
    $cookie='';
    $request=function($query,$data=null,$sendCookie=true)use($base,&$cookie){
        $headers="Content-Type: application/x-www-form-urlencoded\r\n".($sendCookie?"Cookie: $cookie\r\n":'');
        $options=['ignore_errors'=>true,'follow_location'=>0,'header'=>$headers];
        if($data!==null){$options['method']='POST';$options['content']=http_build_query($data);}
        $body=file_get_contents($base.'/login-notice-http-fixture.php?'.http_build_query($query),false,stream_context_create(['http'=>$options]));
        foreach($http_response_header as $header)if(preg_match('/Set-Cookie: ([^;]+)/i',$header,$m))$cookie=$m[1];
        preg_match('/HTTP\/\S+ (\d+)/',$http_response_header[0],$m);return [(int)$m[1],$body];
    };
    foreach(['admin','sponsor','captive'] as $kind){
        [$code,$html]=$request(['kind'=>$kind]);verify($code===200 && str_contains($html,'&lt;script&gt;') && str_contains($html,'name="loginNoticeAccepted"'),'Notice form missing/unsafe: '.$kind);
        preg_match('/name="csrfToken" value="([a-f0-9]+)"/',$html,$m);$token=$m[1];
        $data=['csrfToken'=>$token,'inputUsername'=>'fixture','inputPassword'=>'FixturePassword123','loginNoticeRevision'=>hash('sha256','one')];
        [$code,$body]=$request(['kind'=>$kind,'action'=>'login'],$data);verify($code===400 && !str_contains($body,'AUTHENTICATION_REACHED') && !str_contains($body,'FixturePassword123'),'Missing acceptance allowed or credentials echoed');
        foreach(['0',['1']] as $bad){[$code,$body]=$request(['kind'=>$kind,'action'=>'login'],$data+['loginNoticeAccepted'=>$bad]);verify($code===400,'Malformed acceptance allowed');}
        $accepted=$data+['loginNoticeAccepted'=>'1'];
        [$code,$body]=$request(['kind'=>$kind,'action'=>'login','version'=>'two'],$accepted);verify($code===400,'Stale notice accepted');
        [$code,$body]=$request(['kind'=>$kind,'action'=>'login'],$accepted);verify($code===200 && $body==='AUTHENTICATION_REACHED','Accepted login failed: '.$kind.' '.$body);
        // A new GET re-establishes the CSRF token cleared by normal authentication reset.
        [$code,$html]=$request(['kind'=>$kind,'mode'=>'disabled']);preg_match('/name="csrfToken" value="([a-f0-9]+)"/',$html,$m);$data['csrfToken']=$m[1];
        verify(!str_contains($html,'name="loginNoticeAccepted"'),'Disabled notice rendered');
        [$code,$body]=$request(['kind'=>$kind,'action'=>'login','mode'=>'disabled'],$data);verify($code===200 && $body==='AUTHENTICATION_REACHED','Disabled feature blocked legacy login');
        [$code,$html]=$request(['kind'=>$kind,'action'=>'login','sso'=>1]);verify($code===200 && str_contains($html,'Continue to sign in') && !str_contains($html,'AUTHENTICATION_REACHED'),'SSO skipped consent');
        preg_match_all('/name="csrfToken" value="([a-f0-9]+)"/',$html,$matches);$ssoToken=end($matches[1]);
        $sso=['csrfToken'=>$ssoToken,'loginNoticeAccepted'=>'1','loginNoticeRevision'=>hash('sha256','one')];
        [$code,$body]=$request(['kind'=>$kind,'action'=>'login','sso'=>1],$sso);verify($code===200 && $body==='AUTHENTICATION_REACHED','SSO acknowledgment failed: '.$kind.' '.$body);
        if($kind==='captive')verify(str_contains($html,'sessionId=fixture-session') && str_contains($html,'client_mac=AA%3ABB') && str_contains($html,'redirect=https%3A'),'Captive redirect context lost');
        [$code,$body]=$request(['kind'=>$kind,'action'=>'login','sso'=>1],$sso,false);verify($code===403,'Foreign-session acceptance accepted');
        [$code,$body]=$request(['kind'=>$kind,'action'=>'login','mode'=>'failure']);verify($code===503 && !str_contains($body,'AUTHENTICATION_REACHED'),'Read failure bypassed notice');
    }
});
ob_end_clean();echo "PASS issue 105: actual admin/sponsor/captive forms and login gates, SSO, stale/missing/forged acceptance, CSRF, text safety, admin-only save, audit and disabled defaults\n";
