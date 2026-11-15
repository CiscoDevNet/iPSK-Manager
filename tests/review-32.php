<?php
require __DIR__.'/review-bootstrap.php';
$dir=sys_get_temp_dir().'/ipsk-tls-'.bin2hex(random_bytes(6));mkdir($dir);
$proc=proc_open(['openssl','req','-x509','-newkey','rsa:2048','-nodes','-keyout',"$dir/key",'-out',"$dir/cert",'-days','1','-subj','/CN=localhost','-addext','subjectAltName=DNS:localhost'],[['pipe','r'],['file',"$dir/log",'a'],['file',"$dir/log",'a']],$pipes);verify(proc_close($proc)===0,'Certificate generation');
$port=random_int(20000,50000);
$server=proc_open(['openssl','s_server','-accept',(string)$port,'-cert',"$dir/cert",'-key',"$dir/key",'-www'],[['pipe','r'],['file',"$dir/log",'a'],['file',"$dir/log",'a']],$pipes);
try {
    for($i=0;$i<50;$i++){if($sock=@fsockopen('127.0.0.1',$port)){fclose($sock);break;}usleep(20000);}
    foreach ([['localhost',true,true,true],['127.0.0.1',true,true,false],['localhost',false,true,false],['127.0.0.1',false,false,true]] as [$host,$trusted,$verify,$ok]) {
        $args=[PHP_BINARY];if($trusted){$args[]='-d';$args[]="curl.cainfo=$dir/cert";}
        array_push($args,__DIR__.'/rest-tls-client.php',"https://$host:$port",$verify?'verify':'disable');
        $client=proc_open($args,[['pipe','r'],['pipe','w'],['file',"$dir/log",'a']],$pipes);
        $result=json_decode(stream_get_contents($pipes[1]),true);fclose($pipes[1]);verify(proc_close($client)===0,'Client failed');
        verify($result===['ok'=>$ok,'verify'=>$verify],'TLS trust/hostname/stapling behavior incorrect');
    }
} finally {proc_terminate($server);proc_close($server);foreach(glob("$dir/*") as $f)unlink($f);rmdir($dir);}
echo "PASS review 32: trusted TLS without stapling, invalid chain/hostname rejected, explicit override works\n";
