<?php
require __DIR__.'/review-bootstrap.php';
$dir=sys_get_temp_dir().'/ipsk-cron-'.bin2hex(random_bytes(6));mkdir($dir);mkdir($dir.'/supportfiles');mkdir($dir.'/supportfiles/include');symlink(realpath(__DIR__.'/../supportfiles/include/phpmailer'),$dir.'/supportfiles/include/phpmailer');
$source=file_get_contents(__DIR__.'/../expire-endpoints-cron-example.php.txt');$source=str_replace('"/path/to/iPSK-Manager"',var_export($dir,true),$source);$source=str_replace('<?php',"<?php\nregister_shutdown_function(function(){echo class_exists('PHPMailer\\\\PHPMailer\\\\PHPMailer') && class_exists('PHPMailer\\\\PHPMailer\\\\SMTP') ? 'DEPENDENCIES_OK' : 'MISSING';});",$source);
file_put_contents($dir.'/cron.php',$source);
try{foreach(['/','/tmp',__DIR__.'/..'] as $cwd){$p=proc_open([PHP_BINARY,$dir.'/cron.php'],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes,$cwd);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);verify(proc_close($p)===0 && $out==='DEPENDENCIES_OK' && $err==='','Cron depends on working directory: '.$err);}}finally{unlink($dir.'/cron.php');unlink($dir.'/supportfiles/include/phpmailer');rmdir($dir.'/supportfiles/include');rmdir($dir.'/supportfiles');rmdir($dir);}
echo "PASS review 22: cron dependencies load from configured installation under unrelated working directories\n";
