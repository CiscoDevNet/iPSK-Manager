<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/http-harness.php';
withHttpFixture(function($base) {
    $secret="  special&\"'`<key> ";
    $input=array_fill_keys(['password','confirmpassword','ersPassword','mntPassword','smtpPassword','adPassword','ciscoAVPairPSK','presharedKey'],$secret);
    $output=postSanitizer($base,$input);
    foreach($input as $key=>$value) verify($output[$key]===$value,'Secret bytes changed: '.$key);
    $doc=new DOMDocument();@$doc->loadHTML('<input value="'.ipskHtml($output['ciscoAVPairPSK']).'">');
    verify($doc->getElementsByTagName('input')->item(0)->getAttribute('value')===$secret,'Output escaping changed displayed key');
    verify(password_verify($output['password'],password_hash($secret,PASSWORD_DEFAULT)),'Password hash roundtrip failed');
});
echo "PASS review 14: real HTTP secret input preserves spaces/special characters, authentication and HTML roundtrip\n";
