<?php
require __DIR__.'/review-bootstrap.php';
foreach ([-1=>false, 0=>false, 60=>true] as $offset=>$expected) {
    $_SESSION = validSession() + ['logoutTimer'=>time()+$offset];
    verify(ipskLoginSessionCheck() === $expected, 'Explicit deadline boundary incorrect');
}
foreach ([[1799,43199,true], [1800,2000,false], [0,43200,false]] as [$idle,$age,$expected]) {
    $_SESSION = validSession() + ['lastActivity'=>time()-$idle, 'authenticationTimestamp'=>time()-$age];
    verify(ipskLoginSessionCheck() === $expected, 'Idle/absolute timeout incorrect');
    if (!$expected) verify(empty($_SESSION['authorizationGranted']), 'Expired session retained grant');
}
echo "PASS review 18: deadline, idle and absolute expiry boundaries\n";
