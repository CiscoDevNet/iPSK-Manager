<?php
require __DIR__.'/review-bootstrap.php';
foreach (['aabbccddeeff','aa-bb-cc-dd-ee-ff','AA:BB:CC:DD:EE:FF'] as $mac) verify(ipskCanonicalMac($mac)==='AA:BB:CC:DD:EE:FF','Equivalent MAC differs');
foreach (['','AA:BB-CC:DD:EE:FF','GG:BB:CC:DD:EE:FF','AA:BB:CC:DD:EE','aabbccddeeff00',[]] as $mac) verify(ipskCanonicalMac($mac)===false,'Invalid MAC accepted');
echo "PASS review 30: shared strict MAC validation and canonical representation\n";
