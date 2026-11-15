<?php
require __DIR__.'/review-bootstrap.php';
foreach (['sponsor', 'captive', 'anonymous', 'admin'] as $role) {
    $_SESSION = $role === 'anonymous' ? [] : validSession();
    if ($role === 'admin') $_SESSION['adminAuthorized'] = true;
    verify(ipskAdminSessionCheck() === ($role === 'admin'), 'Admin role isolation failed');
}
foreach (['ajax/getdata.php', 'ajax/getmodule.php', 'ajax/runmigrations.php', 'ajax/fileupload.php', 'adminportal.php'] as $file) {
    $source = file_get_contents(__DIR__.'/../adminportal/'.$file);
    verify(str_contains($source, 'ipskAdminSessionCheck()'), 'Missing admin guard: '.$file);
    verify(str_contains($source, "ipskSessionHandler('admin')"), 'Wrong cookie scope: '.$file);
}
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
ipskSessionHandler('admin');
verify(session_name() === 'IPSKADMIN', 'Admin cookie name');
session_write_close();
ipskSessionHandler();
verify(session_name() === 'IPSKPORTAL', 'Portal cookie name');
session_write_close();
echo "PASS review 02: admin endpoints and session cookie isolation\n";
