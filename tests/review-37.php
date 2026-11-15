<?php
require __DIR__.'/review-bootstrap.php';
$db=new iPSKManagerDatabase();
foreach (['deleteInternalUserGroupMappingById','deleteInternalUserGroupMappingByUserId','deleteInternalGroupById','deleteInternalUserById','deletePortalGroupMappingById'] as $method) {
    verify($db->$method(1)===false && $db->$method(0)===false,'Protected mutation reached SQL');
}
$_SESSION=validSession()+['adminAuthorized'=>true];$sanitizedInput=['id'=>1,'confirmaction'=>1];$ipskISEDB=$db;
foreach (['internalusers','internalgroups'] as $module) {
    ob_start();include __DIR__.'/../supportfiles/adminportals/modules/'.$module.'/delete.inc.php';$out=ob_get_clean();
    verify(http_response_code()===403 && str_contains($out,'Protected'),'Protected direct request accepted');
}
echo "PASS review 37: protected users/groups and mappings rejected at handlers and database boundary\n";
