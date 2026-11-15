<?php
// Run inside the disposable database integration lifecycle.
verify($db->dbSchemaVersion===13,'Unexpected migration fixture baseline');
$c->query('DROP TABLE loginNotices');
verify(!$db->getLoginNotice()['enabled'],'Recovery login unavailable before schema 14');
verify(!$db->saveLoginNotice(1,'Too early','fixture'),'Notice enabled before migration');
verify($db->runSchemaMigrations(14)['success'],'Schema 14 migration failed');
verify(!$db->getLoginNotice()['enabled'],'Migration enabled notice by default');
$noticeText="Fixture café 👋 <script>literal only</script>\n".str_repeat('Long notice & quotes "\' ',250);
verify($db->saveLoginNotice('1',$noticeText,'fixture'),'Long Unicode notice save failed');
$stored=$db->getLoginNotice();verify($stored['enabled'] && $stored['text']===$noticeText,'Stored notice truncated or escaped');
$db->dbSchemaVersion=13;verify($db->runSchemaMigrations(14)['success'],'Migration retry failed');
verify($db->getLoginNotice()===$stored,'Migration retry erased notice/revision');
foreach([[1,''],[1,str_repeat('x',20001)],[[], 'notice']] as [$enabled,$text]){
    verify(!$db->saveLoginNotice($enabled,$text,'fixture') && $db->getLoginNotice()===$stored,'Invalid save changed existing notice');
}
$c->query("CREATE TRIGGER notice_save_failure BEFORE UPDATE ON loginNotices FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected notice save failure'");
verify(!$db->saveLoginNotice(0,'Changed','fixture') && $db->getLoginNotice()===$stored,'Failed save disabled/changed active notice');
$c->query('DROP TRIGGER notice_save_failure');
verify($db->saveLoginNotice(0,$noticeText,'fixture'),'Disable failed');$disabled=$db->getLoginNotice();
verify(!$disabled['enabled'] && $disabled['text']===$noticeText && $disabled['revision']!==$stored['revision'],'Disable lost text or revision');
verify($db->saveLoginNotice(1,$noticeText,'fixture'),'Re-enable failed');
verify($db->getLoginNotice()['revision']!==$stored['revision'],'Re-enable reused stale acceptance revision');
$c->query('RENAME TABLE loginNotices TO fixture_hidden_notice');
$failedClosed=false;try{$db->getLoginNotice();}catch(RuntimeException $error){$failedClosed=true;}
verify($failedClosed,'Unreadable notice treated as disabled');
$c->query('RENAME TABLE fixture_hidden_notice TO loginNotices');
echo "PASS issue 105 integration: migration/default-off/retry, long Unicode notices, atomic failed saves, revisions and read-failure denial\n";
