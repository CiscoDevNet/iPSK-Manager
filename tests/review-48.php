<?php
require __DIR__.'/review-bootstrap.php';
foreach ([[5,1,5],[1,5,5],[5,0,0],[0,5,0]] as [$a,$b,$expected]) {
    $rows=[['endpointGroupId'=>7,'groupPermissions'=>512,'maxDevices'=>$a],['endpointGroupId'=>7,'groupPermissions'=>512,'maxDevices'=>$b],['endpointGroupId'=>7,'groupPermissions'=>2,'maxDevices'=>0]];
    $merged=ipskMergeGroupGrants($rows);
    verify($merged['count']===1 && $merged[0]['maxDevices']===$expected,'Quota depends on grant order or non-create grant');
    verify(ipskQuotaAvailable(3,$merged[0]['maxDevices']),'Merged valid capacity hidden');
}
$m=ipskMergeGroupGrants([['endpointGroupId'=>7,'groupPermissions'=>512,'maxDevices'=>1],['endpointGroupId'=>7,'groupPermissions'=>2048,'maxDevices'=>9]]);
verify($m[0]['maxDevices']===1 && $m[0]['bulkMaxDevices']===9,'Single/bulk grants contaminate each other');
echo "PASS review 48: deterministic greatest permitted quota, unlimited wins, separate create/bulk grants\n";
