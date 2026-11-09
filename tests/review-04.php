<?php
require __DIR__.'/review-bootstrap.php';
class EditAuthorizationStub {
    public $permissions=258, $groups=false, $policies=[], $writes=[];
    function getEndPointAssociationPermissions(...$args) { return [['groupPermissions'=>$this->permissions]]; }
    function getEndpointByAssociationId($id) { return ['endpointId'=>42,'epGroupId'=>7,'createdBy'=>'owner']; }
    function getEndPointGroupAuthorizations(...$args) { return $this->groups; }
    function getAuthorizationTemplatesbyEPGroupId($id) { $this->policies[]=$id; return ['vlan'=>'safe','dacl'=>'safe']; }
    function updateEndpoint(...$args) { $this->writes[]=$args; return true; }
}
foreach (['source-denied','destination-denied','metadata'] as $case) {
    $_SESSION=validSession()+['portalSettings'=>['id'=>3],'authorizationGroups'=>[],'logonSID'=>'owner','editAssociationEndpointId'=>9];
    $ipskISEDB=new EditAuthorizationStub();
    if ($case==='source-denied') $ipskISEDB->permissions=2;
    $sanitizedInput=['id'=>9,'confirmaction'=>1,'fullName'=>'Name','emailAddress'=>'a@example.test','endpointDescription'=>'desc','editAssociation'=>$case==='metadata'?0:1,'associationGroup'=>999,'editPSK'=>0];
    $portalId='test';
    ob_start(); include __DIR__.'/../supportfiles/portals/sponsorportal/default/edit.inc.php'; ob_end_clean();
    if ($case==='metadata') {
        verify($ipskISEDB->policies===[7] && count($ipskISEDB->writes)===1, 'Metadata edit used submitted policy');
    } else verify(!$ipskISEDB->writes && !$ipskISEDB->policies, 'Denied edit read policy or wrote endpoint');
}
echo "PASS review 04: fresh source and destination authorization, existing policy for metadata edits\n";
