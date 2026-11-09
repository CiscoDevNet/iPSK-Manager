<?php
// Only run against a disposable database server; creates and removes test users.
require __DIR__.'/review-bootstrap.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$host=getenv('IPSK_TEST_DB_HOST') ?: 'ipsk-migration-db';
$root=new mysqli($host,'root','');
$suffix=bin2hex(random_bytes(6));
$database='ipsk_recovery_'.$suffix;
$owner='ipsk_owner_'.$suffix;
$other='ipsk_other_'.$suffix;
$app='ipsk_app_'.$suffix;
$password=bin2hex(random_bytes(16));
$created=[];
try {
    $root->query("CREATE DATABASE `$database`");
    $root->select_db($database);
    foreach ([$owner,$other,$app] as $user) {
        $root->query("CREATE USER '$user'@'%' IDENTIFIED BY '$password'");
        $created[]=$user;
    }
    $root->query("GRANT ALL PRIVILEGES ON `$database`.* TO '$app'@'%'");
    $root->query("CREATE TABLE settings (id INT AUTO_INCREMENT PRIMARY KEY, page VARCHAR(32), settingClass VARCHAR(64), keyName VARCHAR(64), optionIndex INT DEFAULT 0, value TEXT, encrypted INT DEFAULT 0)");
    $root->query("INSERT INTO settings (page,settingClass,keyName,value) VALUES ('global','db-schema','version','6')");
    // Matches a failed legacy v7 update: attribute routine absent; other ISE
    // routines share their owner; an unrelated migration test has another owner.
    $root->query("CREATE DEFINER='$owner'@'%' PROCEDURE iPSK_AuthMACPlain() SELECT 1");
    $root->query("CREATE DEFINER='$owner'@'%' PROCEDURE iPSK_FetchGroups() SELECT 1");
    $root->query("CREATE DEFINER='$other'@'%' PROCEDURE iPSK_MigrationTestProc() SELECT 1");
    $db=connectionFor($root); $db->set_dbDatabase($database); $db->dbSchemaVersion=6;
    $r=$db->runSchemaMigrations(7);
    verify($r['success'] && $r['currentVersion']===7, 'Missing attribute routine not restored: '.$r['error']);
    $definition=$root->query('SHOW CREATE PROCEDURE iPSK_AttributeFetch')->fetch_assoc()['Create Procedure'];
    verify(str_contains($definition,"DEFINER=`$owner`@`%`") && str_contains($definition,'subscriberName'), 'Restored definition lost owner or migration change');
    $root->query("CALL iPSK_AttributeFetch('*', @result)");
    while ($root->more_results()) $root->next_result();
    verify((int)$root->query('SELECT @result')->fetch_row()[0]===0, 'Restored routine does not execute');

    // The normal application's schema-wide privileges do not grant the ability
    // to back up another account's routine body. Failure must preserve it.
    $application=new mysqli($host,$app,$password,$database);
    $restricted=connectionFor($application); $restricted->set_dbDatabase($database); $restricted->dbSchemaVersion=6;
    $r=$restricted->runSchemaMigrations(7);
    verify(!$r['success'] && $r['currentVersion']===6, 'Restricted account unexpectedly replaced another owner routine');
    verify(str_contains($r['error'],'alternate DB credentials'), 'Restricted account lacks actionable error: '.$r['error']);
    verify($root->query('SHOW CREATE PROCEDURE iPSK_AttributeFetch')->fetch_assoc()['Create Procedure']===$definition, 'Failed migration changed working routine');
    $application->close();

    // Repair v10 through the same path, then verify an existing target's own
    // definer always wins over inconsistent siblings.
    $root->query('DROP PROCEDURE iPSK_AuthMACPlain');
    $db->dbSchemaVersion=9; $r=$db->runSchemaMigrations(10);
    verify($r['success'], 'Missing plain-MAC routine not restored: '.$r['error']);
    $plain=$root->query('SHOW CREATE PROCEDURE iPSK_AuthMACPlain')->fetch_assoc()['Create Procedure'];
    verify(str_contains($plain,"DEFINER=`$owner`@`%`") && str_contains($plain,'accountEnabled = 1'), 'Plain-MAC recovery lost suspension check or owner');
    $root->query('DROP PROCEDURE iPSK_FetchGroups');
    $root->query("CREATE DEFINER='$other'@'%' PROCEDURE iPSK_FetchGroups() SELECT 1");
    $db->dbSchemaVersion=6; $r=$db->runSchemaMigrations(7);
    verify($r['success'], 'Existing target owner incorrectly rejected because siblings differ');
    $root->query('DROP PROCEDURE iPSK_AttributeFetch');
    $db->dbSchemaVersion=6; $r=$db->runSchemaMigrations(7);
    verify(!$r['success'] && $r['currentVersion']===6 && str_contains($r['error'],'owners differ'), 'Ambiguous missing owner was guessed');
    echo "PASS migration recovery integration: missing v7/v10 routines, original definer, restricted-account failure, executable recovery and ambiguous-owner refusal\n";
} finally {
    $root->query("DROP DATABASE IF EXISTS `$database`");
    foreach ($created as $user) $root->query("DROP USER '$user'@'%'");
}
