<?php
// Loopback test fixture only: no configuration file, live database or identity provider.
require __DIR__.'/review-bootstrap.php';
$kind=$_GET['kind'] ?? 'sponsor';
if (!in_array($kind,['admin','sponsor','captive'],true)) {http_response_code(404);exit;}
$_SERVER['REMOTE_ADDR']='127.0.0.1';
ipskSessionHandler($kind==='admin'?'admin':'portal');
class NoticeAuthenticationReached extends RuntimeException {}
class NoticeHttpDatabase {
    function loginNoticesAvailable(){return true;}
    function saveLoginNotice($enabled,$text,$actor){$_SESSION['fixtureNotice']=ipskLoginNoticeInput($enabled,$text)+['revision'=>bin2hex(random_bytes(32))];return true;}
    function getLoginNotice(){
        if(isset($_SESSION['fixtureNotice']))return $_SESSION['fixtureNotice'];
        if(($_GET['mode']??'')==='failure')throw new RuntimeException('Fixture failure');
        return ['enabled'=>($_GET['mode']??'')!=='disabled','text'=>"Fixture notice <script>window.noticeXss=true</script>\nSecond line & café 👋",'revision'=>hash('sha256',$_GET['version']??'one')];
    }
    function getGlobalClassSetting($name){return ['enabled'=>!empty($_GET['sso']),'usernamefield'=>'REMOTE_USER','headers'=>false,'ldap-source'=>false];}
    function getLdapDirectoryCount(){return 0;}
    function generateLogData(...$args){return '';}
    function addLogEntry(...$args){}
    function authenticateInternalUser($username,$password,$sso=false){
        if($username==='fixture' && ($password==='FixturePassword123' || $sso))throw new NoticeAuthenticationReached();
        return false;
    }
}
$ipskISEDB=new NoticeHttpDatabase();
$portalId='00112233-4455-6677-8899-aabbccddeeff';
$portalSettings=['id'=>7,'portalId'=>$portalId,'portalName'=>'Fixture portal','authenticationDirectory'=>0];
$_SESSION['portalSettings']=$portalSettings;$_SESSION['portalGET']=['client_mac'=>'AA:BB:CC:DD:EE:FF','sessionId'=>'fixture-session','redirect'=>'https://controller.example.test/complete'];
$portalQuery=ipskPortalQuery($portalId,$_SESSION['portalGET']);
$baseSid=$orgSid=$systemSid=$systemSID='fixture';
$sanitizedInput=['inputUsername'=>$_POST['inputUsername']??'','authDirectory'=>0];
$inputPassword=$_POST['inputPassword']??'';
$_SERVER['REMOTE_USER']='fixture';
if(in_array($_GET['action']??'', ['editor','save'],true)){
    $_SESSION=array_merge($_SESSION,validSession(),['adminAuthorized'=>true,'logonSID'=>'fixture-admin','authenticationTimestamp'=>time(),'lastActivity'=>time()]);
    if($_GET['action']==='save'){include __DIR__.'/../supportfiles/adminportals/modules/sysconfig/loginnotice.inc.php';}
    else{print '<!doctype html><html><head><meta charset="utf-8"><title>Notice editor</title><script src="scripts/jquery.min.js"></script></head><body>'.ipskLoginNoticeEditor($ipskISEDB).'</body></html>';}
    return;
}
if(($_GET['action']??'')==='login'){
    try{
        if($kind==='admin'){
            $source=file_get_contents(__DIR__.'/../adminportal/login.php');
            eval(substr($source,strpos($source,'$samlSettings =')));
        }else{include __DIR__.'/../supportfiles/portals/'.($kind==='sponsor'?'sponsorportal':'captiveportal').'/default/login.inc.php';}
    }catch(NoticeAuthenticationReached $error){print 'AUTHENTICATION_REACHED';}
}else{
    if($kind==='admin'){
        try{$loginNotice=$ipskISEDB->getLoginNotice();}catch(Throwable $error){http_response_code(503);exit('Notice unavailable');}
        $source=file_get_contents(__DIR__.'/../adminportal/index.php');
        $html=substr($source,strpos($source,'?><!doctype')+2);
        $html=preg_replace('/<\?php\s*\}\s*\?>\s*$/','',$html);
        eval('?>'.$html);
    }else{include __DIR__.'/../supportfiles/portals/'.($kind==='sponsor'?'sponsorportal':'captiveportal').'/default/index.inc.php';}
}
