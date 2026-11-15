<?php
// Disposable-database integration worker. Never reads application configuration.
require __DIR__.'/review-bootstrap.php';
class EnrollmentDatabase extends iPSKManagerDatabase {
    public $group;
    function getEndPointGroupAuthorizations($portal,$groups){return ['count'=>1,['endpointGroupId'=>$this->group,'groupPermissions'=>2561,'maxDevices'=>4,'bulkMaxDevices'=>4]];}
    function getGlobalClassSetting($name){return ['use-portal-description'=>0];}
    function getSmtpSettings(){return ['enabled'=>0];}
    function getWirelessNetworkById($id){return ['ssidName'=>'Fixture'];}
    function emailEndpointGroup($id){return false;}
    function generateLogData(...$args){return '';}
    function addLogEntry(...$args){return true;}
    function beginPortalEnrollment($group,$macs,$permission=512){
        fwrite(STDOUT,"ready\n");
        if(fgets(STDIN)!=="go\n")throw new RuntimeException('Barrier failed');
        return parent::beginPortalEnrollment($group,$macs,$permission);
    }
}
[$script,$database,$group,$route,$mac]=$argv;
if(!preg_match('/\Aipsk_test_[a-f0-9]{16}\z/',$database))exit(2);
set_error_handler(function($n,$s,$f,$l){throw new ErrorException($s,0,$n,$f,$l);});
$c=new mysqli(getenv('IPSK_TEST_DB_HOST')?:'ipsk-review-db','root','',$database);
$ipskISEDB=new EnrollmentDatabase();(new ReflectionProperty(iPSKManagerDatabase::class,'dbConnection'))->setValue($ipskISEDB,$c);
$ipskISEDB->set_dbDatabase($database);$ipskISEDB->group=(int)$group;
$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_SESSION=validSession()+['logonSID'=>'concurrent-user','fullName'=>'User','portalSettings'=>['id'=>1],'authorizationGroups'=>[], 'portalAuthorization'=>['create'=>true,'bulkcreate'=>true]];
$portalHomeJson=json_encode('index.php?portalId=fixture');$portalId='fixture';$portalQuery='portalId=fixture';$portalSettings=['portalName'=>'Fixture'];
$sanitizedInput=['associationGroup'=>(int)$group,'macAddress'=>$mac,'endpointDescription'=>'Fixture','emailAddress'=>'fixture@example.test','fullName'=>'Fixture','wirelessSSID'=>1,'inputUsername'=>'fixture'];
if($route==='bulk'){
    $token=str_repeat('a',64);$_SESSION['bulk-import-expiry'][$token]=time()+60;
    $_SESSION['bulk-import'][$token]=['count'=>1,['macAddress'=>$mac,'fullname'=>'Fixture','description'=>'','emailaddress'=>'fixture@example.test']];
    $sanitizedInput+=['bulkImportType'=>1,'uploadkey'=>$token,'groupUuid'=>''];
    $file='sponsorportal/default/bulkimport.inc.php';
}else{$file=($route==='captive'?'captiveportal':'sponsorportal').'/default/create.inc.php';}
ob_start();include __DIR__.'/../supportfiles/portals/'.$file;$html=ob_get_clean();
verify(str_contains($html,'successfully'),'Enrollment did not complete');
echo 'committed';
