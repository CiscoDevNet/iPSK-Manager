<?php
require_once __DIR__.'/../supportfiles/include/iPSKManagerFunctions.php';
require_once __DIR__.'/../supportfiles/include/iPSKManagerDatabase.php';
function verify($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
function connectionFor($connection) {
    $db = new iPSKManagerDatabase();
    (new ReflectionProperty($db, 'dbConnection'))->setValue($db, $connection);
    return $db;
}
function validSession() {
    return ['loggedIn'=>true, 'logonDN'=>'test', 'logonUsername'=>'test',
        'authenticationGranted'=>true, 'authorizationGranted'=>true];
}
