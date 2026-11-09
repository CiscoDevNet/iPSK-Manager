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
    $c->query("INSERT INTO sponsorPortalTypes (id,portalTypeName,portalTypeDescription,portalClass,portalModule) VALUES (1,'Fixture','Fixture','core','sponsorportal')");
    $c->query("INSERT INTO sponsorPortals (portalName,description,portalId,portalType,enabled,visible,portalHostname,authenticationDirectory,createdBy) VALUES ('Fixture','','00112233-4455-6677-8899-aabbccddeeff',1,1,1,'fixture','0','fixture')");
    verify($db->getPortalByGuid('00112233-4455-6677-8899-aabbccddeeff') !== false, 'Active portal unavailable');
    $c->query('UPDATE sponsorPortals SET visible=0');
    verify($db->getPortalByGuid('00112233-4455-6677-8899-aabbccddeeff') === false, 'Deleted portal still usable');
    $c->query('UPDATE sponsorPortals SET visible=1,enabled=0');
    verify($db->getPortalByGuid('00112233-4455-6677-8899-aabbccddeeff') === false, 'Disabled portal still usable');

    $sid = "user'sid";
    verify($db->getUserEndpointCount(99, $sid) === 0, 'New user must have zero quota usage');
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
    $c->query("UPDATE endpoints SET expirationDate=100,accountExpired='True'");
    verify($db->updateEndpoint($id,"O'Connor",'fixture','a@example.test',$sid,null,'10',"acl's",0), 'Unlimited update failed');
    $updated=$c->query('SELECT * FROM endpoints WHERE id='.(int)$id)->fetch_assoc();
    verify((int)$updated['expirationDate']===0 && $updated['accountExpired']==='False' && $updated['pskValue']===$psk, 'Zero expiration failed or changed omitted key');
    $db->updateEndpoint($id,"O'Connor",'fixture','a@example.test',$sid,null,'10',"acl's");
    verify((int)$c->query('SELECT expirationDate FROM endpoints WHERE id='.(int)$id)->fetch_row()[0]===0, 'Omitted expiration changed');
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
    $template=$db->addAuthorizationTemplate('Quota policy','','validkey',0,8,'','','fixture');
    $group=$db->addEndpointGroup('Quota group','',$template,0,'fixture');
    verify($template && $group,'Quota fixture creation failed');
    $jobs=[];
    foreach (['AA:BB:CC:DD:01:01','AA:BB:CC:DD:01:02'] as $mac) {
        $process=proc_open([PHP_BINARY,__DIR__.'/quota-worker.php',$name,(string)$group,'1','commit',$mac],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes);
        $jobs[]=[$process,$pipes];
    }
    $out=[];
    foreach ($jobs as [$process,$pipes]) {
        $out[]=stream_get_contents($pipes[1]); $errors=stream_get_contents($pipes[2]);
        foreach($pipes as $pipe) fclose($pipe);
        verify(proc_close($process)===0,'Quota worker failed: '.$errors);
    }
    sort($out); verify($out===['committed','denied'],'Concurrent requests exceeded last quota slot');
    verify($db->getUserEndpointCount($group,'quota-user')===1,'Concurrent quota oversubscribed');
    $runWorker=function ($max,$mode,$macs) use ($name,$group) {
        $p=proc_open([PHP_BINARY,__DIR__.'/quota-worker.php',$name,(string)$group,(string)$max,$mode,$macs],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes);
        $out=stream_get_contents($pipes[1]); $errors=stream_get_contents($pipes[2]);
        foreach($pipes as $pipe) fclose($pipe);
        verify(proc_close($p)===0,'Quota worker failed: '.$errors); return $out;
    };
    verify($runWorker(2,'commit','AA:BB:CC:DD:02:01,AA:BB:CC:DD:02:02')==='denied','Batch ignored incoming size');
    verify($runWorker(2,'rollback','AA:BB:CC:DD:03:01')==='rollback','Failed insertion cleanup');
    verify($c->query("SELECT COUNT(*) FROM endpoints WHERE macAddress='AA:BB:CC:DD:03:01'")->fetch_row()[0]==0,'Rollback left orphan endpoint');
    verify($runWorker(2,'commit','AA:BB:CC:DD:03:01')==='committed','Failed insertion retained reservation');
    verify($runWorker(0,'commit','AA:BB:CC:DD:04:01,AA:BB:CC:DD:04:02')==='committed','Unlimited batch rejected');
    echo "PASS quota integration: independent concurrent connections, batch capacity, rollback/retry, unlimited\n";
    echo "PASS database integration: real schema, prepared queries, literal secrets, settings, group lookup, bulk insert IDs\n";
} finally {
    $c->query("DROP DATABASE `$name`");
}
