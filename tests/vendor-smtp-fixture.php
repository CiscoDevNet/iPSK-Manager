<?php
// Disposable loopback SMTP peer; never relays mail or loads application settings.
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if (!$server) { throw new RuntimeException($error); }
echo substr(strrchr(stream_socket_get_name($server, false), ':'), 1)."\n";
flush();
$client = stream_socket_accept($server, 10);
if (!$client) { throw new RuntimeException('SMTP test client did not connect'); }
stream_set_timeout($client, 10);
fwrite($client, "220 localhost test SMTP\r\n");
$capture = ['commands'=>[], 'message'=>'', 'username'=>'', 'password'=>''];
$auth = 0;
$data = false;
while (($line = fgets($client)) !== false) {
    $command = rtrim($line, "\r\n");
    if ($data) {
        if ($command === '.') {
            $data = false;
            fwrite($client, "250 Message captured locally\r\n");
        } else {
            $capture['message'] .= str_starts_with($line, '..') ? substr($line, 1) : $line;
        }
        continue;
    }
    if ($auth) {
        $capture[$auth === 1 ? 'username' : 'password'] = base64_decode($command, true);
        fwrite($client, $auth === 1 ? "334 UGFzc3dvcmQ6\r\n" : "235 Authenticated\r\n");
        $auth = $auth === 1 ? 2 : 0;
        continue;
    }
    $capture['commands'][] = $command;
    if (str_starts_with($command, 'EHLO')) {
        fwrite($client, "250-localhost\r\n250-STARTTLS\r\n250 AUTH LOGIN\r\n");
    } elseif ($command === 'AUTH LOGIN') {
        $auth = 1;
        fwrite($client, "334 VXNlcm5hbWU6\r\n");
    } elseif ($command === 'STARTTLS') {
        throw new RuntimeException('Explicit NONE encryption attempted STARTTLS');
    } elseif (str_starts_with($command, 'RCPT TO') && ($argv[1] ?? '') === 'reject') {
        fwrite($client, "550 Recipient rejected by test fixture\r\n");
    } elseif ($command === 'DATA') {
        $data = true;
        fwrite($client, "354 End with dot\r\n");
    } elseif ($command === 'QUIT') {
        fwrite($client, "221 Bye\r\n");
        break;
    } else {
        fwrite($client, "250 OK\r\n");
    }
}
fclose($client);
fclose($server);
echo json_encode($capture, JSON_THROW_ON_ERROR)."\n";
