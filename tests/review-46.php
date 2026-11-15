<?php
require __DIR__.'/review-bootstrap.php';
foreach (['psk=beginpsk=abcdefgh'=>'beginpsk=abcdefgh','psk=abcdefgh'=>'abcdefgh','abcdefgh'=>'abcdefgh','psk=psk=psk='=>'psk=psk='] as $stored=>$plain) verify(ipskPskPlain($stored)===$plain,'Embedded prefix removed');
echo "PASS review 46: strip only the storage prefix and preserve embedded psk= text\n";
