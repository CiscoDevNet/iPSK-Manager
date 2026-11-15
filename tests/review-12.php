<?php
require __DIR__.'/review-bootstrap.php';
class ImportCaptured extends RuntimeException {}
class ImportKeyDatabase {
    public $mode='*devicerandom*', $keys, $sequence=0;
    function getGlobalClassSetting($name) { return []; }
    function getEndPointGroupAuthorizations(...$args) { return ['count'=>1,['endpointGroupId'=>7,'groupPermissions'=>2560,'maxDevices'=>0,'bulkMaxDevices'=>0]]; }
    function getUserEndpointCount(...$args) { return 0; }
    function getAuthorizationTemplatesbyEPGroupId($id) { return ['ciscoAVPairPSK'=>$this->mode,'termLengthSeconds'=>0,'pskLength'=>12,'vlan'=>'','dacl'=>'']; }
    function generateRandomPassword($length) { return str_pad((string)++$this->sequence,$length,'a'); }
    function getUserPreSharedKey(...$args) { return 'user-secret'; }
    function beginPortalEnrollment(...$args) { return true; }
    function addBulkEndpoints($mac,$names,$desc,$email,$keys,...$rest) { $this->keys=$keys; throw new ImportCaptured(); }
    function generateLogData(...$args) {return '';}
    function addLogEntry(...$args) {}
}
$ipskISEERS=new class {function getEndPointsByEPGroup($id) {return json_encode(['SearchResult'=>['total'=>2,'resources'=>[['name'=>'AA:BB:CC:DD:EE:01'],['name'=>'AA:BB:CC:DD:EE:02']]]]);}};
foreach (['adminportals/modules/endpoints','portals/sponsorportal/default'] as $path) {
    foreach ([1,3] as $source) foreach (['*devicerandom*','*userrandom*','common-secret'] as $mode) {
        (function () use ($path,$source,$mode,$ipskISEERS) {
        $_SERVER['REMOTE_ADDR']='127.0.0.1';
        $_SESSION=validSession()+['adminAuthorized'=>true,'logonSID'=>'owner','portalSettings'=>['id'=>3],'authorizationGroups'=>[],'portalAuthorization'=>['bulkcreate'=>true],
            'bulk-import-expiry'=>[str_repeat('a',64)=>time()+60], 'bulk-import'=>[str_repeat('a',64)=>['count'=>2,['macAddress'=>'AA:BB:CC:DD:EE:01','fullname'=>'One','description'=>'','emailaddress'=>''],['macAddress'=>'AA:BB:CC:DD:EE:02','fullname'=>'Two','description'=>'','emailaddress'=>'']]]];
        $sanitizedInput=['associationGroup'=>7,'wirelessSSID'=>1,'bulkImportType'=>$source,'emailAddress'=>'a@example.test','fullName'=>'Name','endpointDescription'=>'','groupUuid'=>'fixture','uploadkey'=>str_repeat('a',64)];
        $ipskISEDB=new ImportKeyDatabase(); $ipskISEDB->mode=$mode;
        ob_start(); try { include __DIR__.'/../supportfiles/'.$path.'/bulkimport.inc.php'; } catch (ImportCaptured $e) {} finally {ob_end_clean();}
        if ($mode==='*devicerandom*') verify(is_array($ipskISEDB->keys) && count(array_unique($ipskISEDB->keys))===2 && strlen($ipskISEDB->keys[0])===16,'Device keys missing/reused');
        else verify($ipskISEDB->keys==='psk='.($mode==='*userrandom*'?'user-secret':$mode),'Shared key mode changed');
        })();
    }
}
echo "PASS review 12: actual admin/sponsor CSV and ISE import handlers across all PSK modes\n";
