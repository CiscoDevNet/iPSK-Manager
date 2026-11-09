<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/upload-harness.php';$_SESSION=validSession()+['adminAuthorized'=>true];
foreach(['admin','sponsor'] as $portal){
    foreach(['macaddress,fullname,fullname,description','macaddress,macaddress,macaddress,macaddress','macaddress,fullname,emailaddress','macaddress,fullname,emailaddress,description,extra'] as $header){$before=$_SESSION['bulk-import']??[];$r=runUpload($portal,$header."\nAA:BB:CC:DD:EE:FF,Owner,owner@example.test,Test");verify(!$r['result'] && ($_SESSION['bulk-import']??[])===$before,'Invalid header changed pending imports');}
    $r=runUpload($portal,"DESCRIPTION,EmailAddress,FullName,MacAddress\nTest,owner@example.test,Owner,AA:BB:CC:DD:EE:FF");verify($r['result'] && $r['validitems']===1,'Unique reordered header rejected');$row=$_SESSION['bulk-import'][$r['uploadkey']][0];verify($row['fullname']==='Owner' && $row['emailaddress']==='owner@example.test' && $row['macAddress']==='AA:BB:CC:DD:EE:FF','Header mapping incorrect');
}
echo "PASS review 24: duplicate/missing/extra CSV headers rejected; case-insensitive reordered headers map correctly\n";
