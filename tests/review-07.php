<?php
require __DIR__.'/review-bootstrap.php';
$_SESSION=[]; $token=ipskCsrfToken();
verify(!ipskCsrfValid(null) && !ipskCsrfValid([]) && !ipskCsrfValid(str_repeat('x',64)), 'Bad token accepted');
verify(ipskCsrfValid($token), 'Valid token rejected');
$html=ipskCsrfHtml('<head></head><form method="post"></form><form method="get"></form>',$token);
verify(substr_count($html,'name="csrfToken"')===1 && str_contains($html,'X-CSRF-Token'), 'Form/AJAX token plumbing missing');
$_SESSION=[]; ipskCsrfToken(); verify(!ipskCsrfValid($token),'Foreign session token accepted');
$port=random_int(20000,50000);
$proc=proc_open([PHP_BINARY,'-S','127.0.0.1:'.$port,'-t',__DIR__],[['pipe','r'],['file','/tmp/ipsk-csrf-http.log','a'],['file','/tmp/ipsk-csrf-http.log','a']],$pipes);
try {
    $url='http://127.0.0.1:'.$port.'/csrf-http-fixture.php';
    for ($i=0;$i<50;$i++) { $html=@file_get_contents($url); if ($html!==false) break; usleep(20000); }
    verify($html!==false,'HTTP fixture did not start');
    $headers=$http_response_header;
    preg_match('/name="csrfToken" value="([a-f0-9]+)"/',$html,$m); $real=$m[1];
    foreach($headers as $header) if(preg_match('/Set-Cookie: ([^;]+)/i',$header,$m)) $cookie=$m[1];
    foreach ([null,'invalid',$real] as $sent) {
        $context=stream_context_create(['http'=>['method'=>'POST','ignore_errors'=>true,'header'=>"Cookie: $cookie\r\nContent-Type: application/x-www-form-urlencoded",'content'=>http_build_query(['csrfToken'=>$sent])]]);
        $body=file_get_contents($url,false,$context);
        verify(($body==='WRITE')===($sent===$real),'HTTP mutation token check failed');
    }
    $context=stream_context_create(['http'=>['method'=>'POST','ignore_errors'=>true,'header'=>"Cookie: $cookie\r\nX-CSRF-Token: $real",'content'=>'']]);
    verify(file_get_contents($url,false,$context)==='WRITE','AJAX token rejected');
} finally { proc_terminate($proc); proc_close($proc); }
echo "PASS review 07: live HTTP missing/invalid/valid form and AJAX tokens, session binding, UI wiring\n";
