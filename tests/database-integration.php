<?php
// Only for an isolated disposable database. Never reads application configuration.
require __DIR__.'/review-bootstrap.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$c = new mysqli(getenv('IPSK_TEST_DB_HOST') ?: 'ipsk-review-db', 'root', '');
$name = 'ipsk_test_'.bin2hex(random_bytes(8));
$c->query("CREATE DATABASE `$name`");
$c->select_db($name);
try {
    $source = str_replace("\r\n", "\n", file_get_contents(__DIR__.'/../adminportal/installer.inc.php'));
    preg_match_all('/^\s*\$(?:sqlTable|sqlAlterTable|sqlConstraint)\[\d+\] = .*;$/m', $source, $matches);
    eval(implode("\n", $matches[0]));
    foreach (array_merge($sqlTable, $sqlAlterTable, $sqlConstraint) as $sql) $c->query($sql);
    $db = connectionFor($c);
    $sid = "user'sid";
    $psk = "psk=O'Connor&\\quote";
    $id = $db->addEndpoint('AA:BB:CC:DD:EE:FF', "O'Connor", 'fixture', 'a@example.test', $psk, '10', "acl's", 0, $sid);
    verify($id > 0, 'Insert ID lost');
    $row = $c->query('SELECT * FROM endpoints')->fetch_assoc();
    verify($row['pskValue'] === $psk && $row['createdBy'] === $sid && $row['dacl'] === "acl's", 'Special characters changed');
    verify($db->addEndpoint('AA:BB:CC:DD:EE:FF', 'Other', '', '', 'psk=otherkey', '', '', 0, $sid) == $id, 'Duplicate lookup broken');
    $db->setGlobalSetting("fixture's", "key's", "value's");
    verify($db->getGlobalSetting("fixture's", "key's") === "value's", 'Settings insert roundtrip');
    $db->setGlobalSetting("fixture's", "key's", "updated's");
    verify($db->getGlobalSetting("fixture's", "key's") === "updated's", 'Settings update roundtrip');
    $groups = ['count'=>2, ['groupDn'=>"CN=O'Connor"], ['groupDn'=>"' OR 1=1 --"]];
    $db->getEndPointGroupAuthorizations(1, $groups);
    $db->getEndPointAssociationPermissions(1, $groups, 1);
    $db->getEndPointAssociationList($groups, 1);
    $db->getEndPointAssociationList($groups, 1, true, "CN=O'Connor");
    $db->getPortalByGuid('00112233-4455-6677-8899-aabbccddeeff');
    $db->getPortalbyHostname("host' OR 1=1 --");
    $batch = $db->addBulkEndpoints(['AA:BB:CC:DD:EE:01','AA:BB:CC:DD:EE:02'], ['One',"Two's"], ['',''], ['',''], [$psk,$psk], '', '', 0, $sid);
    verify($batch['processed'] === 2, 'Bulk insert failed');
    foreach ([0,1] as $index) {
        $row = $c->query('SELECT * FROM endpoints WHERE id='.(int)$batch[$index]['insertId'])->fetch_assoc();
        verify($row['macAddress'] === $batch[$index]['macAddress'], 'Bulk returned wrong record');
        verify($row['pskValue'] === $psk, 'Bulk secret changed');
    }
    echo "PASS database integration: real schema, prepared queries, literal secrets, settings, group lookup, bulk insert IDs\n";
} finally {
    $c->query("DROP DATABASE `$name`");
}
