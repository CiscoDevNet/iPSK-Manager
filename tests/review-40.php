<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/mail-stubs.php';
ReviewMail\loadFunctions(__DIR__.'/../supportfiles/include/email.php','function htmlBody(');
$_SERVER['REMOTE_ADDR']='127.0.0.1';$_SESSION['logonUsername']='tester';$_SESSION['logonSID']='tester-sid';
$db=new ReviewMail\Log;
verify(ReviewMail\sendHTMLEmail('owner@example.test','Portal','Password8','SSID','AA:BB:CC:DD:EE:FF','Group','Description','Owner','Sponsor',['smtp-fromaddress'=>'sender@example.test'],$db),'Local mail failed');
$headers=explode("\r\n",$GLOBALS['mailArgs'][3]);verify(in_array('MIME-Version: 1.0',$headers,true),'MIME-Version is not its own header');verify(in_array('Content-Type: text/html; charset=ISO-8859-1',$headers,true),'HTML content type lost');
echo "PASS review 40: local HTML mail has independent MIME-Version and Content-Type headers\n";
