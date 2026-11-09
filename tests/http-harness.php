<?php
function withHttpFixture($callback) {
    $port=random_int(20000,50000);
    $process=proc_open([PHP_BINARY,'-d','display_errors=0','-S','127.0.0.1:'.$port,'-t',__DIR__],[['pipe','r'],['file','/tmp/ipsk-http.log','a'],['file','/tmp/ipsk-http.log','a']],$pipes);
    try {
        $base='http://127.0.0.1:'.$port;
        for($i=0;$i<50;$i++) { $socket=@fsockopen('127.0.0.1',$port); if($socket){fclose($socket);break;} usleep(20000); }
        $callback($base);
    } finally {proc_terminate($process);proc_close($process);}
}
function postSanitizer($base,$input) {
    $context=stream_context_create(['http'=>['method'=>'POST','header'=>'Content-Type: application/x-www-form-urlencoded','content'=>http_build_query($input)]]);
    return json_decode(file_get_contents($base.'/sanitize-http-fixture.php',false,$context),true,512,JSON_THROW_ON_ERROR);
}
