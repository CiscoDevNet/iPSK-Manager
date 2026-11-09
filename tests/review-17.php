<?php
require __DIR__.'/review-bootstrap.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
ipskSessionHandler();
$old = session_id();
$_SESSION['marker'] = 'preserved';
ipskRotateSession();
$new = session_id();
verify($new !== $old, 'Session identifier did not rotate');
verify($_SESSION['marker'] === 'preserved', 'Rotation lost session context');
verify(!file_exists(session_save_path().'/sess_'.$old), 'Pre-login session still exists');
$options = session_get_cookie_params();
verify($options['httponly'] && $options['samesite'] === 'Lax', 'Cookie protections absent');
session_destroy();
echo "PASS review 17: rotation destroys old session and retains context, protected cookies\n";
