<?php
require __DIR__.'/review-bootstrap.php';
class ViewPermissionStub {
    public $permissions=0, $owner='other';
    function getEndPointAssociationPermissions(...$args) { return [['groupPermissions'=>$this->permissions]]; }
    function getEndpointByAssociationId($id) { return ['createdBy'=>$this->owner]; }
    function getEndPointAssociationById($id) { throw new RuntimeException('Metadata read without authorization'); }
}
$ipskISEDB = new ViewPermissionStub();
foreach ([0,1] as $bits) {
    $_SESSION=validSession()+['portalSettings'=>['id'=>3],'authorizationGroups'=>[],'logonSID'=>'owner'];
    $ipskISEDB->permissions=$bits; $sanitizedInput=['id'=>9];
    ob_start(); include __DIR__.'/../supportfiles/portals/sponsorportal/default/view.inc.php'; $out=ob_get_clean();
    verify(http_response_code()===403 && str_contains($out,'not authorized'), 'Unrelated detail request accepted');
}
$ipskISEDB->owner='owner';
verify(ipskEndpointPermission($ipskISEDB,9), 'Own detail view denied');
$ipskISEDB->owner='other'; $ipskISEDB->permissions=2;
verify(ipskEndpointPermission($ipskISEDB,9), 'Group detail view denied');
echo "PASS review 10: detail authorization precedes metadata retrieval, ownership and group controls\n";
