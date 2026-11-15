<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/upload-harness.php';require __DIR__.'/http-harness.php';$_SESSION=validSession()+['adminAuthorized'=>true];
foreach(['admin','sponsor'] as $portal){$tokens=[];for($i=0;$i<10;$i++){$r=runUpload($portal,"macaddress,fullname,emailaddress,description\nAA:BB:CC:DD:EE:FF,Owner$i,owner@example.test,Test");$tokens[]=$r['uploadkey'];}verify(count(array_unique($tokens))===10,'Rapid uploads collided');foreach($tokens as $i=>$token){verify(ipskTakePendingImport($token)[0]['fullname']==='Owner'.$i,'Wrong upload consumed');verify(ipskTakePendingImport($token)===false,'Token reused');}}
$token=ipskStorePendingImport(['count'=>1]);$_SESSION['bulk-import-expiry'][$token]=time()-1;verify(ipskTakePendingImport($token)===false,'Expired token accepted');
$token=ipskStorePendingImport(['count'=>0]);withHttpFixture(function($base)use($token){verify(postSanitizer($base,['uploadkey'=>$token])['uploadkey']===$token,'Upload token filtered out');});
echo "PASS review 52: rapid uploads stay independent, tokens roundtrip HTTP, expire and consume once\n";
