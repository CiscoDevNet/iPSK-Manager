<?php
// Only for an isolated disposable database. Never reads application configuration.
require __DIR__.'/review-bootstrap.php';
ob_start();
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
    verify($db->addEndpoint('aa-bb-cc-dd-ee-ff', 'Other', '', '', 'psk=otherkey', '', '', 0, $sid) == $id, 'Duplicate lookup broken');
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
    foreach ([['AA:BB:CC:DD:09:01','AA:BB:CC:DD:09:01'],['aa-bb-cc-dd-09-02','AA:BB:CC:DD:09:02']] as $duplicates) {
        $before=(int)$c->query('SELECT COUNT(*) FROM endpoints')->fetch_row()[0];
        verify($db->addBulkEndpoints($duplicates,['One','Conflicting'],['',''],['',''],$psk,'','',0,$sid)===false,'Duplicate batch accepted');
        verify(str_contains($db->lastError,'Duplicate MAC'),'Missing actionable duplicate error');
        verify((int)$c->query('SELECT COUNT(*) FROM endpoints')->fetch_row()[0]===$before,'Rejected duplicate batch wrote records');
    }
    $c->query('SET SESSION auto_increment_increment=2');
    $batch = $db->addBulkEndpoints(['AA:BB:CC:DD:EE:01','AA:BB:CC:DD:EE:02'], ['One',"Two's"], ['',''], ['',''], [$psk,$psk], '', '', 0, $sid);
    verify($batch['processed'] === 2, 'Bulk insert failed');
    foreach ([0,1] as $index) {
        $row = $c->query('SELECT * FROM endpoints WHERE id='.(int)$batch[$index]['insertId'])->fetch_assoc();
        verify($row['macAddress'] === $batch[$index]['macAddress'], 'Bulk returned wrong record');
        verify($row['pskValue'] === $psk, 'Bulk secret changed');
    }
    $c->query('SET SESSION auto_increment_increment=1');
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
    // Release two actual enrollment handlers together immediately before GET_LOCK.
    $db->addAuthorizationTemplate('Concurrent user keys','','*userrandom*',0,16,'','','fixture');
    $keyPolicy=(int)$c->insert_id;
    foreach ([['sponsor','sponsor'],['captive','captive'],['sponsor','bulk'],['bulk','bulk']] as $case=>$routes) {
        $db->addEndpointGroup('Concurrent keys '.$case,'',$keyPolicy,0,'fixture');$keyGroup=(int)$c->insert_id;
        $jobs=[];
        foreach ($routes as $index=>$route) {
            $mac=sprintf('AA:BB:DD:00:%02X:%02X',$case,$index);
            $process=proc_open([PHP_BINARY,__DIR__.'/enrollment-worker.php',$name,(string)$keyGroup,$route,$mac],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes);
            stream_set_timeout($pipes[1],20);$jobs[]=[$process,$pipes];
        }
        foreach($jobs as [$process,$pipes])verify(fgets($pipes[1])==="ready\n",'Enrollment failed before barrier');
        foreach($jobs as [$process,$pipes]){fwrite($pipes[0],"go\n");fclose($pipes[0]);}
        foreach($jobs as [$process,$pipes]){
            $out=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
            verify(proc_close($process)===0 && $out==='committed' && $errors==='','Concurrent enrollment failed: '.$out.$errors);
        }
        $row=$c->query('SELECT COUNT(*) AS devices,COUNT(DISTINCT e.pskValue) AS keysUsed FROM endpoints e JOIN endpointAssociations a ON a.endpointId=e.id WHERE a.epGroupId='.$keyGroup)->fetch_assoc();
        verify((int)$row['devices']===2 && (int)$row['keysUsed']===1,'Concurrent first enrollments split the user key');
    }
    echo "PASS review 58 integration: simultaneous sponsor, captive, mixed single/bulk and bulk/bulk enrollments share one key\n";
    echo "PASS quota integration: independent concurrent connections, batch capacity, rollback/retry, unlimited\n";
    $user=$db->addInternalUser('UniqueUser','First','','',password_hash('validpass',PASSWORD_DEFAULT),'fixture');
    verify($user>0 && $db->addInternalUser('uniqueuser','Duplicate','','',password_hash('otherpass',PASSWORD_DEFAULT),'fixture')===false,'Duplicate username accepted');
    $other=$db->addInternalUser('OtherUser','Other','','',password_hash('otherpass',PASSWORD_DEFAULT),'fixture');
    verify(!$db->updateInternalUser($other,'UNIQUEUSER','Other','','','fixture'),'Duplicate rename accepted');
    verify($db->authenticateInternalUser('UniqueUser','validpass'),'Unique user cannot authenticate');
    verify($_SESSION['fullName']==='First' && $_SESSION['memberOf']['count']===0,'Authentication selected wrong identity or failed empty membership');
    $c->query("INSERT INTO internalGroups (id,groupName,groupType,description,groupDn,permissions,createdBy) VALUES (2,'AdminFixture',0,'','CN=AdminFixture',1,'fixture')");
    $c->query('INSERT INTO internalUserGroupMapping (userId,groupId,createdBy) VALUES ('.(int)$other.",2,'fixture')");
    verify(!$db->canDeleteInternalObject('user',$other) && !$db->canDeleteInternalObject('group',2),'Last administrator deletion allowed');
    $c->query('INSERT INTO internalUserGroupMapping (userId,groupId,createdBy) VALUES ('.(int)$user.",2,'fixture')");
    verify($db->canDeleteInternalObject('user',$other),'Ordinary administrator deletion blocked despite remaining admin');
    verify($db->updateInternalUserGroupMapping([],$other,'fixture'),'Final membership removal failed');
    verify($c->query('SELECT COUNT(*) FROM internalUserGroupMapping WHERE userId='.(int)$other)->fetch_row()[0]==0,'Membership remained');
    verify(!$db->updateInternalUserGroupMapping(['invalid'],$other,'fixture'),'Malformed group accepted');
    verify($db->authenticateInternalUser('OtherUser','otherpass') && $_SESSION['memberOf']['count']===0,'Fresh login retained removed group');
    $sponsor=$db->addSponsorGroup('Atomic group','',0,[],0,0,'fixture');
    verify($db->addSponsorGroupEPGMapping([$group],$sponsor,'fixture'),'Atomic mapping fixture');
    foreach ([MYSQLI_REPORT_OFF, MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT] as $reporting) {
        mysqli_report($reporting);
        verify(!$db->updateSponsorGroupEPGMapping([999999],$sponsor,'fixture'),'FK failure reported success');
        verify((int)$c->query('SELECT endpointGroupId FROM sponsorGroupEPGMapping WHERE sponsorGroupId='.(int)$sponsor)->fetch_row()[0]===(int)$group,'Failed mapping replacement deleted original');
        $c->query("CREATE TRIGGER review_fail_sid BEFORE UPDATE ON internalUsers FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected SID failure'");
        $before=(int)$c->query('SELECT COUNT(*) FROM internalUsers')->fetch_row()[0];
        verify(!$db->addInternalUser('AtomicUser','Name','','','hash','fixture'),'Second write failure reported success');
        verify((int)$c->query('SELECT COUNT(*) FROM internalUsers')->fetch_row()[0]===$before,'Failed SID assignment left partial user');
        $c->query('DROP TRIGGER review_fail_sid');
        $c->query("CREATE TRIGGER review_fail_setting BEFORE INSERT ON settings FOR EACH ROW BEGIN IF NEW.keyName='admin-portal-strict-hostname' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected setting failure'; END IF; END");
        $_SESSION=validSession()+['adminAuthorized'=>true];http_response_code(200);
        $input=['module-action'=>'general','adminPortalHostname'=>'atomic.example.test','strict-hostname'=>1,'redirect-hostname'=>1,'logPurgeInterval'=>30,'usePortalDescription'=>1];
        ob_start();$ok=ipskRenderDatabaseOperation($db,__DIR__.'/../supportfiles/adminportals/modules/sysconfig/update.inc.php',['ipskISEDB'=>$db,'sanitizedInput'=>$input]);$output=ob_get_clean();
        verify(!$ok && !str_contains($output,'atomic.example.test'),'Batch settings failure reported success');
        verify((int)$c->query("SELECT COUNT(*) FROM settings WHERE settingClass='admin-portal'")->fetch_row()[0]===0,'Batch settings left partial changes');
        $c->query('DROP TRIGGER review_fail_setting');
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    verify($db->updateSponsorGroupEPGMapping([],$sponsor,'fixture'),'Empty mapping removal failed');
    echo "PASS review 20 integration: mapping/SID/settings failures roll back under strict and non-strict mysqli\n";
    $db->set_dbDatabase($name);
    $c->query('CREATE PROCEDURE iPSK_AttributeFetch (IN username VARCHAR(64), OUT result INT) SELECT 1');
    $runner=$db->getMigrationManager();
    $migration=$runner->getMigrations()[0];
    $upgrade=$runner->executeMigration($migration);
    verify($upgrade['success'],'Routine upgrade failed: '.$upgrade['error']);
    $routine=$c->query('SHOW CREATE PROCEDURE iPSK_AttributeFetch')->fetch_assoc();
    verify(str_contains($routine['Create Procedure'],'subscriberName'),'Routine replacement missing subscriberName');
    $c->query("INSERT INTO endpoints (macAddress,password,expirationDate,fullName,description,emailAddress,pskValue,createdBy) VALUES ('aa-bb-cc-dd-ee-ff','aabbccddeeff',0,'Collision','','','psk=validkey','fixture')");
    $collisionId=$c->insert_id;
    $v8=$runner->getMigrations()[1];
    verify(!$runner->executeMigration($v8)['success'],'Canonical collision migration must fail before updates');
    verify($c->query('SELECT macAddress FROM endpoints WHERE id='.(int)$id)->fetch_row()[0]==='AA:BB:CC:DD:EE:FF','Failed migration changed data');
    $c->query('DELETE FROM endpoints WHERE id='.(int)$collisionId);
    $c->query("UPDATE endpoints SET macAddress='aabbccddeeff' WHERE id=".(int)$id);
    $db->dbSchemaVersion=7;
    $migrationResult=$db->runSchemaMigrations(8);
    verify($migrationResult['success'] && $migrationResult['currentVersion']===8,'Canonical migration failed: '.$migrationResult['error']);
    verify($c->query('SELECT macAddress FROM endpoints WHERE id='.(int)$id)->fetch_row()[0]==='AA:BB:CC:DD:EE:FF','Legacy MAC not normalized');
    $c->query('ALTER TABLE internalUsers DROP INDEX unique_internal_username');
    $c->query("INSERT INTO internalUsers (userName,password,fullName,createdBy) VALUES ('uniqueuser','invalid','Legacy duplicate','fixture')");
    $duplicateUser=$c->insert_id;
    verify(!$db->authenticateInternalUser('UniqueUser','validpass'),'Ambiguous legacy identity authenticated');
    verify(!$db->runSchemaMigrations(9)['success'] && $db->dbSchemaVersion===8,'Duplicate migration claimed success');
    $c->query('DELETE FROM internalUsers WHERE id='.(int)$duplicateUser);
    verify($db->runSchemaMigrations(9)['success'],'Resolved duplicate migration did not recover');
    // Exercise both fresh-install and migrated routines against actual MariaDB results.
    preg_match('/\$sqlProcedure\[1\] = <<< SQL\n(.*?)\nSQL;/s', $source, $plainMatch);
    $plainSql=preg_replace('/CREATE DEFINER=.*? PROCEDURE/', 'CREATE PROCEDURE', $plainMatch[1]);
    $callPlain=function($mac,$password)use($c){$s=$c->prepare('CALL iPSK_AuthMACPlain(?,?)');$s->bind_param('ss',$mac,$password);$s->execute();$result=$s->get_result()->fetch_row();while($s->more_results()){$s->next_result();}$s->close();return (int)$result[0];};
    foreach (['fresh','migrated'] as $mode) {
        $c->query('DROP PROCEDURE IF EXISTS iPSK_AuthMACPlain');
        $c->query($mode==='fresh'?$plainSql:str_replace(' AND endpoints.accountEnabled = 1','',$plainSql));
        if($mode==='migrated')verify($db->runSchemaMigrations(10)['success'],'Suspension migration failed');
        foreach ([0,time()-100,time()+10000] as $expiry) foreach ([0,1] as $enabled) {
            $c->query('UPDATE endpoints SET accountEnabled='.$enabled.',expirationDate='.$expiry.' WHERE id='.(int)$id);
            verify(($callPlain('aa-bb-cc-dd-ee-ff','aabbccddeeff')===0)===(bool)$enabled,'Plain auth ignores suspension or changes expiration semantics');
        }
        verify($callPlain('AA:BB:CC:DD:EE:FF','incorrect')!==0 && $callPlain('00:00:00:00:00:01','000000000001')!==0,'Unknown/wrong password accepted');
    }
    echo "PASS review 29 integration: fresh/migrated plain MAC authentication honors suspension, preserves expiry semantics\n";
    $db->updateAuthorizationTemplate($template,'Move policy','','*userrandom*',0,12,'','','fixture');
    $db->addEndpointGroup('Move target','',$template,0,'fixture');$target=(int)$c->insert_id;
    $peer=$db->addEndpoint('AA:BB:CC:00:47:01','Owner peer','','','psk=OwnerTargetKey','','',0,$sid);
    verify($db->addEndpointAssociation($peer,'AA:BB:CC:00:47:01',$target,'actor'),'Owner peer association');
    verify($db->addEndpointAssociation($id,'AA:BB:CC:DD:EE:FF',$group,'actor'),'Move source association');$association=(int)$c->insert_id;
    verify($c->query('SELECT createdBy FROM endpointAssociations WHERE id='.$association)->fetch_row()[0]===$sid,'New association ignores canonical owner');
    $db->addSponsorGroupEPGMapping([$group,$target],$sponsor,'fixture');
    $db->addSponsorInternalGroupMapping([2],$sponsor,1|2|256|512|1024,'fixture');
    $db->addSponsorGroupPortalMapping([$sponsor],1,'fixture');
    $c->query('UPDATE sponsorGroups SET maxDevices=2 WHERE id='.(int)$sponsor);
    $moveViaHandler=function($portal,$destination)use($db,$association){
        $_SESSION=validSession()+['adminAuthorized'=>true,'logonSID'=>'actor','portalSettings'=>['id'=>1],'authorizationGroups'=>['count'=>1,['groupDn'=>'CN=AdminFixture']],'editAssociationEndpointId'=>$association];
        http_response_code(200);
        $input=['id'=>$association,'confirmaction'=>1,'fullName'=>'Moved owner device','emailAddress'=>'owner@example.test','endpointDescription'=>'Moved','editAssociation'=>1,'associationGroup'=>$destination,'editPSK'=>0];
        $path=__DIR__.'/../supportfiles/'.($portal?'portals/sponsorportal/default':'adminportals/modules/endpoints').'/edit.inc.php';
        ob_start();$ok=ipskRenderDatabaseOperation($db,$path,['ipskISEDB'=>$db,'sanitizedInput'=>$input,'portalId'=>'fixture']);$output=ob_get_clean();return $ok;
    };
    verify($moveViaHandler(false,$target),'Admin move failed');
    verify($db->getUserEndpointCount($target,$sid)===2 && $db->getUserEndpointCount($target,'actor')===0,'Admin move transferred quota ownership');
    verify($db->getUserPreSharedKey($target,$sid)==='OwnerTargetKey','Move lost owner per-user key');
    verify($moveViaHandler(true,$group),'Authorized sponsor move failed');
    $after=$c->query('SELECT a.createdBy AS associationOwner,e.createdBy AS endpointOwner,a.epGroupId FROM endpointAssociations a JOIN endpoints e ON e.id=a.endpointId WHERE a.id='.$association)->fetch_assoc();
    verify($after['associationOwner']===$sid && $after['endpointOwner']===$sid && (int)$after['epGroupId']===(int)$group,'Sponsor move changed owner or association ID');
    $c->query('UPDATE sponsorGroups SET maxDevices=1 WHERE id='.(int)$sponsor);
    verify(!$moveViaHandler(true,$target),'Destination owner quota ignored');
    $before=$c->query('SELECT * FROM endpoints WHERE id='.(int)$id)->fetch_assoc();
    $c->query("CREATE TRIGGER review_fail_move BEFORE UPDATE ON endpointAssociations FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected move failure'");
    verify(!$moveViaHandler(false,$target),'Failed move reported success');
    verify($c->query('SELECT * FROM endpoints WHERE id='.(int)$id)->fetch_assoc()===$before,'Failed move partially updated endpoint');
    $c->query('DROP TRIGGER review_fail_move');
    $c->query("UPDATE endpointAssociations SET createdBy='legacy-actor' WHERE id=".$association);
    verify($db->runSchemaMigrations(11)['success'],'Owner reconciliation migration failed');
    verify($c->query('SELECT createdBy FROM endpointAssociations WHERE id='.$association)->fetch_row()[0]===$sid,'Legacy owner mismatch not reconciled');
    $c->query('UPDATE sponsorGroupInternalMapping SET groupPermissions=257 WHERE sponsorGroupId='.(int)$sponsor);
    $_SESSION['logonSID']=$sid;
    verify($db->getEndPointAssociationPermissions($association,['count'=>1,['groupDn'=>'CN=AdminFixture']],1)!==false,'Owner lost own-only visibility');
    $_SESSION['logonSID']='actor';
    verify($db->getEndPointAssociationPermissions($association,['count'=>1,['groupDn'=>'CN=AdminFixture']],1)===false,'Actor acquired own-only visibility');
    echo "PASS review 47 integration: admin/sponsor moves preserve owner, own-key/quota, stable ID; failure rollback and legacy repair\n";
    $db->set_encryptionKey(base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)));
    $primary=$db->addLdapServer('Trusted directory','directory1','same-domain','service','service','dc=fixture',0,1,'fixture',0);
    $secondary=$db->addLdapServer('Independent directory','directory2','same-domain','service','service','dc=fixture',0,1,'fixture',0);
    $alias=$db->addLdapServer('Trusted alternate','directory3','same-domain','service','service','dc=fixture',0,1,'fixture',$primary);
    verify($primary && $secondary && $alias,'Directory fixtures failed');
    verify($db->getLdapSettings($alias)['identitySourceId']===$primary && $db->getLdapSettings($secondary)['identitySourceId']===$secondary,'Configured identity source not resolved');
    verify(!$db->setDirectoryIdentitySource($primary,$alias),'Alias cycle accepted');
    $dn='cn=Managers,dc=fixture';
    verify(!$db->addInternalGroup('Unbound',1,'',$dn,1,'fixture'),'Unbound external group accepted');
    $external=$db->addInternalGroup('Trusted managers',1,'',$dn,1,'fixture',$primary);
    verify($external && $db->getPortalAdminGroups($primary)['count']===1 && $db->getPortalAdminGroups($secondary)['count']===0,'Admin groups cross identity sources');
    verify(!$db->setDirectoryIdentitySource($primary,$secondary) && !$db->deleteLdapServerById($primary),'Referenced identity source changed/deleted');
    $db->addSponsorInternalGroupMapping([$external],$sponsor,513,'fixture');
    $db->addWirelessNetwork('Scoped SSID','','fixture');$wireless=(int)$c->insert_id;
    $db->addSponsorGroupSSIDMapping([$wireless],$sponsor,'fixture');
    $groups=['count'=>1,['groupDn'=>$dn]];
    foreach ([$primary=>true,$secondary=>false,$db->getDirectoryIdentitySource($alias)=>true] as $scope=>$allowed) {
        $_SESSION=validSession();$_SESSION['identitySourceId']=$scope;$_SESSION['logonSID']=$sid;
        verify(($db->getPortalAuthGroups(1)['count']>0)===$allowed,'Portal authentication grant crossed source');
        verify(($db->getEndPointGroupAuthorizations(1,$groups)!==false)===$allowed,'Endpoint groups crossed source');
        verify(($db->getEndPointAssociationPermissions($association,$groups,1)!==false)===$allowed,'Endpoint permissions crossed source');
        verify(($db->getEndPointAssociationList($groups,1)!==false)===$allowed,'Endpoint listing crossed source');
        verify(($db->getPortalWirelessNetworkAuthorization(1,$groups)!==false)===$allowed,'SSID grants crossed source');
    }
    $secondGrant=$db->addInternalGroup('Independent managers',1,'',$dn,1,'fixture',$secondary);
    verify($secondGrant && $db->getPortalAdminGroups($secondary)['count']===1,'Explicit independent administrator grant rejected');
    $db->addSponsorInternalGroupMapping([$secondGrant],$sponsor,513,'fixture');
    $_SESSION['identitySourceId']=$secondary;
    verify($db->getPortalAuthGroups(1)['count']===1,'Explicit independent portal grant rejected');
    // Emulate a legacy database and a retry after the first ALTER was already committed.
    $c->query('ALTER TABLE internalGroups DROP COLUMN directoryId');
    $c->query('ALTER TABLE ldapServers DROP COLUMN identitySourceId');
    verify($db->authenticateInternalUser('UniqueUser','validpass') && $db->getPortalAdminGroups(0)['count']>0,'Internal recovery login blocked before schema migration');
    $c->query('ALTER TABLE ldapServers ADD COLUMN identitySourceId INT NULL DEFAULT NULL');
    verify($db->runSchemaMigrations(12)['success'],'Directory binding migration/retry failed');
    verify($db->getPortalAdminGroups($primary)['count']===0 && $db->getPortalAuthGroups(1,$primary)['count']===0,'Legacy external grant trusted without review');
    verify($db->updateInternalGroup($external,'Trusted managers',1,'',$dn,1,'fixture',$primary),'Reviewed binding could not be saved');
    verify($db->getPortalAdminGroups($primary)['count']===1,'Reviewed grant unavailable');
    $db->dbSchemaVersion=11;verify($db->runSchemaMigrations(12)['success'],'Migration not retryable after version persistence failure');
    verify($db->getPortalAdminGroups($primary)['count']===1,'Migration retry erased reviewed bindings');
    echo "PASS review 56 integration: independent/aliased sources, all portal grants, explicit bindings and fail-closed retryable migration\n";
    echo "PASS migration integration: resolves existing definer without environment and replaces routine successfully\n";
    echo "PASS database integration: real schema, prepared queries, literal secrets, settings, group lookup, bulk insert IDs\n";
} finally {
    $c->query("DROP DATABASE `$name`");
}
