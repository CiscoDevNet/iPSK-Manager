<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/http-harness.php';require __DIR__.'/../supportfiles/include/BaseRestClass.php';
withHttpFixture(function($base){$rest=new BaseRESTCalls($base);foreach(['GET','POST','PUT','DELETE'] as $method){foreach([200,400] as $code){ob_start();$r=$rest->restCall('/rest-http-fixture.php?status='.$code,$method,[],false,'test payload');$output=ob_get_clean();verify($output==='' && $r['http_code']===$code,'REST output leaked or status lost');$body=json_decode($r['body'],true);verify($body['method']===$method,'Response not captured');if(in_array($method,['POST','PUT']))verify($body['body']==='test payload','Payload lost');}}});
echo "PASS review 33: capture success/error bodies and statuses without output for every supported method\n";
