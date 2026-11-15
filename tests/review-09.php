<?php
require __DIR__.'/review-bootstrap.php';
$db=new iPSKManagerDatabase();
$secrets=['smtpPassword'=>'smtp-SECRET','password'=>'password-SECRET','ciscoAVPairPSK'=>'psk-SECRET',
 'HTTP_COOKIE'=>'cookie-SECRET','HTTP_AUTHORIZATION'=>'header-SECRET','ENC_KEY'=>'encryption-SECRET','csrfToken'=>'csrf-SECRET'];
$_POST=$_GET=$_SESSION=$_SERVER=['nested'=>$secrets,'normal'=>'retained', 'headers'=>['Authorization: Basic header-SECRET']];
for ($flags=0;$flags<32;$flags++) {
    $db->loggingLevel=$flags<<4;
    $out=$db->generateLogData(['_POST'=>$_POST],['other'=>['deep'=>$secrets]]);
    verify(!str_contains($out,'SECRET'),'Secret escaped central redaction with flags '.$flags);
    if ($flags) verify(str_contains($out,'retained') || str_contains($out,'REDACTED'),'Logging unexpectedly empty');
}
verify($_POST['nested']['smtpPassword']==='smtp-SECRET','Redaction mutated application input');
echo "PASS review 09: recursive redaction under all 32 debug logging option combinations\n";
