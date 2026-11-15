<?php
require __DIR__.'/../supportfiles/include/iPSKManagerFunctions.php';
ipskSessionHandler();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { echo 'WRITE'; return; }
echo '<html><head></head><body><form method="post"></form></body></html>';
