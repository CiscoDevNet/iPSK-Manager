<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/mail-stubs.php';
ReviewMail\loadFunctions(__DIR__.'/../expire-endpoints-cron-example.php.txt','function sendEmail(');
$db=new ReviewMail\Log;
foreach([true,false] as $result){$GLOBALS['mailResult']=$result;verify(ReviewMail\sendHTMLEmail('owner@example.test',['AA:BB:CC:DD:EE:FF'],'Expiry notice',['smtp-fromaddress'=>'sender@example.test'],$db)===$result,'Wrong send result');verify($GLOBALS['mailArgs'][1]==='Expiry notice' && str_contains($GLOBALS['mailArgs'][2],'AA:BB:CC:DD:EE:FF') && str_contains($GLOBALS['mailArgs'][3],'From: sender@example.test'),'Shifted subject/settings arguments');verify(str_contains(end($db->entries),$result?'EMAIL:SUCCESS':'EMAIL:FAILURE'),'Wrong logging object or result');}
echo "PASS review 21: cron local-mail subject/settings/logger and transport success/failure\n";
