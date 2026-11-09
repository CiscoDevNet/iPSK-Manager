<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/ldap-stubs.php';require __DIR__.'/http-harness.php';ob_start();
class Login56Complete extends RuntimeException {}
class Directory56Connection {
    public $grants=[1],$calls=[];
    function prepare($sql){return new class($this,$sql){public $field_count=1;private $values=[];function __construct(private $db,private $sql){}function bind_param($types,&...$values){$this->values=$values;return true;}function execute(){verify(str_contains($this->sql,'directoryId=? AND groupType=?'),'Admin grant query missing directory scope');$this->db->calls[]=$this->values;return true;}function close(){}function get_result(){$rows=in_array($this->values[0],$this->db->grants,true)?[['groupDn'=>'cn=Managers,dc=fixture']]:[];return new class($rows){public $num_rows;function __construct(private $rows){$this->num_rows=count($rows);}function fetch_assoc(){return array_shift($this->rows);}};}};}
}
class Directory56Database extends iPSKManagerDatabase {
    function getLdapDirectoryCount(){return 3;}
    function getLdapSettings($id){if(!in_array($id,[1,2,3],true))return false;return ['adServer'=>'directory'.$id,'adDomain'=>'same-domain','adUsername'=>'service','adPassword'=>'service','adBaseDN'=>'dc=fixture','adSecure'=>false,'directoryType'=>1,'identitySourceId'=>$id===3?1:$id];}
    function getGlobalClassSetting($class){return $class==='ldap-settings'?['ldap-ssl-check'=>false,'nested-groups'=>false]:['log-purge-interval'=>''];}
    function generateLogData(...$args){return '';}
    function addLogEntry(...$args){return true;}
    function addUserCacheEntry(...$args){return true;}
}
$GLOBALS['ldapBind']=fn($connection,$dn,$password)=>$dn==='service'?$password==='service':$password===($connection==='ldap://directory2'?'second-password':'first-password');
$GLOBALS['ldapEntries']=function($filter,$connection){return ['count'=>1,0=>['dn'=>'uid=alice,dc=fixture','uid'=>['alice'],'mail'=>['alice@example.test'],'givenname'=>['Alice'],'sn'=>['Example'],'memberof'=>['count'=>1,$GLOBALS['differentGroup']?'cn=Guests,dc=fixture':'cn=Managers,dc=fixture']]];};
$source=file_get_contents(__DIR__.'/../adminportal/login.php');$start=strpos($source,'if($sanitizedInput["inputUsername"] != "" && $inputPassword != "" && is_numeric($sanitizedInput["authDirectory"]))');$dispatch=str_replace('die();','throw new Login56Complete();',substr($source,$start));
foreach([[1,[1],false,false,true],[2,[1],false,false,false],[3,[1],false,false,true],[2,[1,2],false,false,true],[1,[1],true,false,false],[1,[1],false,true,false],[1,[0],false,false,false],[99,[1],false,false,false]] as [$directory,$grants,$badPassword,$different,$expected]){
    $_SESSION=[];$GLOBALS['differentGroup']=$different;$c=new Directory56Connection();$c->grants=$grants;$ipskISEDB=new Directory56Database();(new ReflectionProperty(iPSKManagerDatabase::class,'dbConnection'))->setValue($ipskISEDB,$c);
    $sanitizedInput=['inputUsername'=>'alice','authDirectory'=>$directory];$inputPassword=$badPassword?'wrong':($directory===2?'second-password':'first-password');$samlLogin=false;$systemSID='fixture';
    try{eval($dispatch);}catch(Login56Complete $e){}
    verify(($_SESSION['adminAuthorized']??false)===$expected,'Directory collision/alias grant incorrect');
    if($expected)verify($_SESSION['identitySourceId']===($directory===3?1:$directory),'Authenticated scope not bound');
}
withHttpFixture(function($base){$r=postSanitizer($base,['directoryId'=>'2','identitySourceId'=>'1']);verify($r['directoryId']===2 && $r['identitySourceId']===1,'Scope selectors lost in HTTP sanitizer');});
$_SESSION=validSession()+['adminAuthorized'=>true];$ipskISEDB=new class {function getAuthDirectoryNames(){return [0=>'Internal',1=>'Trusted <directory>',2=>'Independent'];}};ob_start();include __DIR__.'/../supportfiles/adminportals/modules/internalgroups/add.inc.php';$html=ob_get_clean();$dom=new DOMDocument();@$dom->loadHTML($html);$xp=new DOMXPath($dom);verify($xp->query('//select[@id="directoryId"]/option')->length===3,'External group scope selector absent');
ob_end_clean();echo "PASS review 56: real admin login rejects directory collisions; explicit aliases/grants work; wrong-password/unrelated/internal grants denied\n";
