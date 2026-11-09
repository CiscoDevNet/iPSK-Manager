<?php
require __DIR__.'/../supportfiles/include/BaseRestClass.php';
$r=new BaseRESTCalls($argv[1]);$r->set_SSLVerifyPeer($argv[2]==='verify');
$result=$r->restCall('/','GET',[]);
echo json_encode(['ok'=>$result['body']!==false,'verify'=>$r->get_SSLVerifyPeer()]);
