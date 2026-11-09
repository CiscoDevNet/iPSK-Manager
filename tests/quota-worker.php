<?php
require __DIR__.'/review-bootstrap.php';
class QuotaDatabase extends iPSKManagerDatabase {
    public $groupId, $maximum;
    function getEndPointGroupAuthorizations($portal, $groups) {
        return ['count'=>1,['endpointGroupId'=>$this->groupId,'groupPermissions'=>2560,'maxDevices'=>$this->maximum,'bulkMaxDevices'=>$this->maximum]];
    }
}
[$script,$database,$group,$maximum,$mode,$macs] = $argv;
if (!preg_match('/\Aipsk_test_[a-f0-9]{16}\z/', $database)) exit(2);
$c=new mysqli(getenv('IPSK_TEST_DB_HOST') ?: 'ipsk-review-db','root','',$database);
$db=new QuotaDatabase(); (new ReflectionProperty(iPSKManagerDatabase::class,'dbConnection'))->setValue($db,$c);
$db->set_dbDatabase($database); $db->groupId=(int)$group; $db->maximum=(int)$maximum;
$_SESSION=validSession()+['logonSID'=>'quota-user','portalSettings'=>['id'=>1],'authorizationGroups'=>[]];
$macs=explode(',',$macs);
if (!$db->beginPortalEnrollment((int)$group,$macs,count($macs)>1?2048:512)) { echo 'denied'; exit; }
usleep(100000); // Ensure two independent connections overlap around the last slot.
foreach ($macs as $mac) {
    $id=$db->addEndpoint($mac,'Quota','','','psk=validkey','','',0,'quota-user');
    if ($mode==='rollback') { $db->abortPortalEnrollment(); echo 'rollback'; exit; }
    if (!$id || !$db->addEndpointAssociation($id,$mac,(int)$group,'quota-user')) { $db->abortPortalEnrollment(); exit(3); }
}
echo $db->finishPortalEnrollment() ? 'committed' : 'failed';
