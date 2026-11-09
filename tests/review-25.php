<?php
require __DIR__.'/review-bootstrap.php';
class ActiveGrants {
    public $grants;
    function getPortalAuthGroups($id) { return $this->grants; }
    function getEndPointGroupAuthorizations(...$args) { return ['count'=>0]; }
    function getPortalWirelessNetworkAuthorization(...$args) { return false; }
}
$db=new ActiveGrants();
$_SESSION=validSession()+['memberOf'=>['CN=member','count'=>1],'authorizedEPGroups'=>[99],'portalAuthorization'=>['create'=>true]];
$db->grants=[['groupDn'=>'CN=member','groupPermissions'=>1], 'count'=>1];
verify(ipskRefreshPortalAuthorization($db,3) && !$_SESSION['portalAuthorization']['create'], 'Changed grant did not revoke cached create permission');
$db->grants=false;
verify(!ipskRefreshPortalAuthorization($db,3) && !ipskLoginSessionCheck(), 'Removed final grant left session authorized');
echo "PASS review 25: refresh cached permissions and revoke sessions after final grant removal\n";
