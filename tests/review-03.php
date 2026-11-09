<?php
require __DIR__.'/review-bootstrap.php';
class CreationGrants {
    public $groups, $count = 0;
    function getEndPointGroupAuthorizations($portal, $groups) { return $this->groups; }
    function getUserEndpointCount($group, $sid) { return $this->count; }
}
$db = new CreationGrants();
foreach (['sponsorportal/default/create', 'captiveportal/default/create', 'sponsorportal/default/bulkimport'] as $handler) {
    foreach (['forged','no-permission','full','lookup-failure'] as $case) {
        $_SESSION = validSession() + ['portalSettings'=>['id'=>3], 'logonSID'=>'user', 'authorizationGroups'=>[]];
        $db->groups = ['count'=>1, ['endpointGroupId'=>7,'maxDevices'=>1,'groupPermissions'=>$case==='no-permission'?1:2560]];
        $db->count = $case==='lookup-failure' ? false : ($case==='full'?1:0);
        $sanitizedInput=['associationGroup'=>$case==='forged'?99:7];
        $ipskISEDB=$db;
        ob_start(); include __DIR__.'/../supportfiles/portals/'.$handler.'.inc.php'; $out=ob_get_clean();
        verify(http_response_code() === 403 && str_contains($out,'not authorized'), 'Unauthorized creation reached handler');
    }
}
$db->count=0;
verify(ipskCreationGrant($db,7,512)!==false, 'Valid create grant rejected');
$db->groups[0]['maxDevices']=0; $db->count=100;
verify(ipskCreationGrant($db,7,2048)!==false, 'Unlimited bulk grant rejected');
echo "PASS review 03: exact fresh group grant, create permission and quota in all creation handlers\n";
