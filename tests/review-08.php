<?php
require __DIR__.'/review-bootstrap.php';
class CoaDatabase {
    public $owner='user';
    function getEndPointAssociationByEndpoint($id) { return 7; }
    function getEndpointByAssociationId($id) { return ['createdBy'=>$this->owner,'macAddress'=>'AA:BB:CC:DD:EE:FF']; }
}
$ipskISEDB=new CoaDatabase();
$ipskISEMNT=new class {
    public $targets=[];
    function getISEMntHostname() { return 'fixture'; }
    function invokeSessionCoADisconnectShutdown($host,$mac) { $this->targets[]=$mac; return ''; }
};
$iseMNTIntegrationAvailable=true;
foreach (['missing-enrollment','foreign-owner','forged-mac','valid'] as $case) {
    $_SESSION=validSession()+['logonSID'=>'user','portalGET'=>['client_mac'=>$case==='forged-mac'?'AA:BB:CC:DD:EE:01':'AA:BB:CC:DD:EE:FF']];
    if ($case!=='missing-enrollment') $_SESSION['enrolledEndpointId']=42;
    $ipskISEDB->owner=$case==='foreign-owner'?'other':'user';
    include __DIR__.'/../supportfiles/portals/captiveportal/default/performcoa.inc.php';
}
verify($ipskISEMNT->targets===['AA:BB:CC:DD:EE:FF'],'Unauthorized target reached MnT');
echo "PASS review 08: CoA restricted to owned verified enrollment and matching client MAC\n";
