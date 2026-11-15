<?php
require __DIR__.'/../supportfiles/include/iPSKManagerFunctions.php';
header('Content-Type: application/json');
echo json_encode(sanitizeGetModuleInput($subModuleRegEx));
