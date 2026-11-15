<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/http-harness.php';require __DIR__.'/../supportfiles/include/BaseRestClass.php';
foreach(['','?body=1'] as $mode)withHttpFixture(function($base)use($mode){$rest=new BaseRESTCalls($base);$rest->set_timeouts(100,250);$start=microtime(true);$r=$rest->restCall('/rest-stall-fixture.php'.$mode,'GET',[]);verify(microtime(true)-$start<1.2,'Stalled transfer exceeded deadline');verify($r['body']===false && $r['error_code']===CURLE_OPERATION_TIMEDOUT && $r['error']!=='','Missing timeout details');});
$rest=new BaseRESTCalls('http://127.0.0.1:1');$rest->set_timeouts(100,250);$start=microtime(true);$r=$rest->restCall('/','GET',[]);verify($r['body']===false && $r['error_code']!==0 && microtime(true)-$start<1.2,'Unreachable peer not bounded');
foreach([[0,100],[200,100],[1,300001]] as [$a,$b]){try{$rest->set_timeouts($a,$b);throw new Exception('Invalid deadline accepted');}catch(InvalidArgumentException $e){}}
echo "PASS review 43: stalled headers/body and unreachable peer bounded; actionable transport errors\n";
