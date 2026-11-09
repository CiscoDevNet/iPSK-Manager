<?php
require __DIR__.'/review-bootstrap.php';
$_SESSION = validSession() + ['adminAuthorized'=>true, 'authorizedEPGroups'=>[1], 'authorizationGroups'=>[1],
    'authorizedWirelessNetworks'=>[1], 'memberOf'=>[1], 'upload'=>['secret'], 'portalSettings'=>['id'=>3], 'portalGET'=>['client_mac'=>'test']];
ipskResetIdentity();
verify(!ipskLoginSessionCheck() && !ipskAdminSessionCheck(), 'Previous identity remains authorized');
foreach (['adminAuthorized', 'authorizedEPGroups', 'authorizationGroups', 'authorizedWirelessNetworks', 'memberOf', 'upload'] as $key) {
    verify(!isset($_SESSION[$key]), 'Old cached identity data remains: '.$key);
}
verify($_SESSION['portalSettings']['id'] === 3 && $_SESSION['portalGET']['client_mac'] === 'test', 'Login navigation context lost');
$db = connectionFor(new class {
    function real_escape_string($value) { return $value; }
    function prepare($sql) { return false; }
});
$_SESSION = validSession() + ['adminAuthorized'=>true];
verify(!$db->authenticateInternalUser('missing', 'wrong'), 'Failed authentication must fail');
verify(!ipskAdminSessionCheck() && !ipskLoginSessionCheck(), 'Failed login retained old grant');
echo "PASS review 27: reauthentication clears all previous identity grants and cached resources\n";
