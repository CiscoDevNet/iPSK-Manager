<?php
// Check every deployed vendor copy and exercise the real mail classes offline.
function vendorVerify($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
$root = dirname(__DIR__);
$manifest = json_decode(file_get_contents($root.'/supportfiles/vendor-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($manifest['assets'] as $asset) {
    foreach ($asset['paths'] as $path) {
        vendorVerify(hash_file('sha256', $root.'/'.$path) === $asset['sha256'], 'Vendor copy changed or out of sync: '.$path);
    }
}
chdir($root.'/adminportal');
require $root.'/supportfiles/include/email.php';
vendorVerify(PHPMailer\PHPMailer\PHPMailer::VERSION === PHPMailer\PHPMailer\SMTP::VERSION, 'Mail class versions differ');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION = ['logonUsername'=>'test', 'logonSID'=>'test'];
foreach (['authenticated', 'anonymous', 'reject'] as $mode) {
    $process = proc_open([PHP_BINARY, __DIR__.'/vendor-smtp-fixture.php', $mode], [['pipe','r'], ['pipe','w'], ['pipe','w']], $pipes);
    vendorVerify(is_resource($process), 'Could not start local SMTP fixture');
    try {
        stream_set_timeout($pipes[1], 15);
        $port = trim(fgets($pipes[1]));
        vendorVerify(ctype_digit($port), 'SMTP fixture did not report a port');
        $settings = ['smtp-hostname'=>'127.0.0.1', 'smtp-port'=>(int)$port, 'smtp-encryption'=>'NONE',
            'smtp-username'=>$mode === 'anonymous' ? '' : "test'&user", 'smtp-password'=>$mode === 'anonymous' ? '' : "test'&password",
            'smtp-fromaddress'=>'sender@example.test'];
        $log = new class {
            public $messages = [];
            function addLogEntry($message, ...$context) { $this->messages[] = $message; }
        };
        $key = "Keep'&<this>Key";
        sendSMTPEmail("test_user+tag@example.test", 'Fixture', $key, 'Test WiFi', 'AA:BB:CC:DD:EE:FF', 'Test group', 'Test device', 'Test user', 'Test sponsor', $settings, $log);
        $capture = json_decode(stream_get_contents($pipes[1]), true, 512, JSON_THROW_ON_ERROR);
        vendorVerify(!in_array('STARTTLS', $capture['commands'], true), 'NONE unexpectedly negotiated TLS');
        if ($mode !== 'anonymous') {
            vendorVerify($capture['username'] === $settings['smtp-username'] && $capture['password'] === $settings['smtp-password'], 'SMTP credentials changed');
        }
        if ($mode === 'reject') {
            vendorVerify(str_contains(implode('', $log->messages), 'EMAIL:FAILURE') && $capture['message'] === '', 'Rejected recipient reported success');
        } else {
            vendorVerify(str_contains(implode('', $log->messages), 'EMAIL:SUCCESS'), 'Enrollment email failed');
            $message = quoted_printable_decode($capture['message']);
            vendorVerify(str_contains($message, 'Subject: Fixture Enrollment'), 'Enrollment subject missing');
            vendorVerify(str_contains($message, 'multipart/alternative') && str_contains($message, 'text/plain') && str_contains($message, 'text/html'), 'MIME alternatives missing');
            vendorVerify(str_contains($message, $key) && str_contains($message, htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')), 'Plain or HTML PSK was changed');
            vendorVerify(str_contains($message, 'test_user+tag@example.test'), 'Recipient changed');
        }
        $errors = stream_get_contents($pipes[2]);
        vendorVerify($errors === '', 'SMTP fixture diagnostics: '.$errors);
    } finally {
        foreach ($pipes as $pipe) { fclose($pipe); }
        proc_terminate($process);
        proc_close($process);
    }
}
echo "PASS vendor libraries: deployed checksums and real PHPMailer authenticated/anonymous delivery, MIME, secret preservation and rejected recipients\n";
