<?php
require __DIR__.'/review-bootstrap.php';
foreach ([['client_mac'=>'" onfocus="alert(1)'],['sessionId'=>'</script><script>alert(1)</script>'],['redirect'=>'javascript:alert(1)'],['redirect'=>"https://example.test/\r\nX-Test: bad"],['client_mac'=>[]]] as $bad) {
    verify(ipskPortalParameters($bad)===false,'Unsafe captive parameter accepted');
}
$input=['client_mac'=>'aa-bb-cc-dd-ee-ff','sessionId'=>'abc123','redirect'=>'https://example.test/path?q="</script>&x=1'];
$p=ipskPortalParameters($input);
verify($p!==false && $p['client_mac']==='AA:BB:CC:DD:EE:FF','Valid captive context rejected');
$query=ipskPortalQuery('00112233-4455-6677-8899-aabbccddeeff',$p);
verify(!str_contains($query,'<') && !str_contains($query,'"'),'Query contains markup delimiters');
parse_str($query,$decoded);
verify($decoded['redirect']===$input['redirect'],'Redirect query did not roundtrip');
$js=json_encode('index.php?'.$query,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
verify(!str_contains($js,'</script>'),'Script termination possible');
echo "PASS review 06: captive parameter validation, encoded query roundtrip and safe JavaScript URL\n";
