<?php
// Included by database-integration.php inside its disposable database lifecycle.
require __DIR__.'/ldap-stubs.php';
$db->set_encryptionKey(base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)));
$ownSource=$db->addLdapServer('Owner source','owner-primary','ownership.test','service','service','dc=fixture',0,1,'fixture',0);
$otherSource=$db->addLdapServer('Other owner source','owner-other','ownership.test','service','service','dc=fixture',0,1,'fixture',0);
$aliasSource=$db->addLdapServer('Owner alias','owner-alias','alias.test','service','service','dc=fixture',0,1,'fixture',$ownSource);
$ownerSponsor=$db->addSponsorGroup('Owner access','',0,[],0,0,'fixture');
$db->addSponsorGroupPortalMapping([$ownerSponsor],1,'fixture');$db->addSponsorGroupEPGMapping([$group],$ownerSponsor,'fixture');
foreach([$ownSource,$otherSource] as $scope){
    $ownerGroup=$db->addInternalGroup('Owner group '.$scope,1,'','cn=Owners,dc=fixture',0,'fixture',$scope);
    verify($db->addSponsorInternalGroupMapping([$ownerGroup],$ownerSponsor,1|8|64|512,'fixture'),'Owner grant fixture');
}
$GLOBALS['ldapEntries']=fn()=>['count'=>1,0=>['dn'=>'uid=alice,dc=fixture','uid'=>['alice'],'mail'=>['alice@example.test'],'givenname'=>['Alice'],'sn'=>['Fixture'],'memberof'=>['count'=>1,'cn=Owners,dc=fixture']]];
$GLOBALS['ldapBind']=fn($connection,$dn,$password)=>$dn==='service'?$password==='service':$password===($connection==='ldap://owner-other'?'second-password':'first-password');
$ownerLogin=function($source,$server,$domain,$password)use($db){
    $_SESSION=[];$ldap=new BaseLDAPInterface($server,$domain,'service','service','dc=fixture',false,false,1,false,$source);
    verify($ldap->authenticateUser('alice',$password),'Owner authentication failed');
    $_SESSION['portalSettings']=['id'=>1];$_SESSION['authorizationGranted']=true;
    verify(ipskRefreshPortalAuthorization($db,1) && ipskLoginSessionCheck(),'Owner portal authorization failed');
};
$ownerLogin($ownSource,'owner-primary','ownership.test','first-password');$scopedOwner=$_SESSION['logonSID'];
$ownerEndpoint=$db->addEndpoint('AA:DD:00:00:00:01','Owner','','','psk=OwnerKey123','','',0,$scopedOwner);
verify($db->addEndpointAssociation($ownerEndpoint,'AA:DD:00:00:00:01',$group,$scopedOwner),'Owner endpoint association');$ownerAssociation=(int)$c->insert_id;
verify(ipskEndpointPermission($db,$ownerAssociation,64),'Owner denied');
$ownerLogin($otherSource,'owner-other','ownership.test','second-password');
verify(!ipskEndpointPermission($db,$ownerAssociation,8) && !ipskEndpointPermission($db,$ownerAssociation,64),'Independent directory acquired PSK/delete rights');
verify($db->getUserPreSharedKey($group,$_SESSION['logonSID'])===false && $db->getUserEndpointCount($group,$_SESSION['logonSID'])===0,'Independent directory shares key/quota');
$ownerLogin($ownSource,'owner-alias','alias.test','first-password');
verify($_SESSION['logonSID']===$scopedOwner && ipskEndpointPermission($db,$ownerAssociation,64),'Explicit alias lost ownership');
verify($db->getUserPreSharedKey($group,$_SESSION['logonSID'])==='OwnerKey123','Alias cannot reuse key');

// Legacy reconciliation: ambiguous ownership fails before changing any endpoint.
$uniqueSource=$db->addLdapServer('Unique owner source','unique','unique.test','service','service','dc=fixture',0,1,'fixture',0);
$db->addLdapServer('Unique owner alias','unique-alias','unique.test','service','service','dc=fixture',0,1,'fixture',$uniqueSource);
$db->addLdapServer('Unique owner label','unique-label','old-label.test','service','service','dc=fixture',0,1,'fixture',$uniqueSource);
$legacyRows=[];
foreach(['alice-ownership.test','bob-unique.test','bob-old-label.test','unmapped-deleted-directory','josé-unique.test'] as $index=>$legacyOwner){
    $mac=sprintf('AA:DD:00:00:01:%02X',$index);
    $ep=$db->addEndpoint($mac,'Legacy','','','psk=LegacyKey123','','',0,$legacyOwner);
    verify($db->addEndpointAssociation($ep,$mac,$group,$legacyOwner),'Legacy association');
    $legacyRows[$legacyOwner]=$ep;$db->addUserCacheEntry($legacyOwner,'legacy@example.test',explode('-',$legacyOwner)[0],'uid=fixture','fixture');
}
$before=$c->query('SELECT id,createdBy,pskValue FROM endpoints ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$failed=$db->runSchemaMigrations(13);
verify(!$failed['success'] && $db->dbSchemaVersion===12,'Ambiguous legacy owners auto-assigned');
verify($before===$c->query('SELECT id,createdBy,pskValue FROM endpoints ORDER BY id')->fetch_all(MYSQLI_ASSOC),'Ambiguous preflight changed endpoints');
// Simulate reviewed, per-endpoint assignment by the DBA; never merge independent sources.
$c->query("UPDATE endpoints SET createdBy='".$scopedOwner."' WHERE id=".$legacyRows['alice-ownership.test']);
$c->query("CREATE TRIGGER sweep_owner_failure BEFORE UPDATE ON endpointAssociations FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected owner update failure'");
verify(!$db->runSchemaMigrations(13)['success'],'Injected migration error reported success');
verify($c->query('SELECT createdBy FROM endpoints WHERE id='.$legacyRows['bob-unique.test'])->fetch_row()[0]==='bob-unique.test','Failed migration left partial owner update');
$c->query('DROP TRIGGER sweep_owner_failure');
verify($db->runSchemaMigrations(13)['success'],'Owner migration failed');
verify($c->query('SELECT createdBy FROM endpoints WHERE id='.$legacyRows['josé-unique.test'])->fetch_row()[0]===ipskOpenLdapOwner($uniqueSource,'josé'),'Non-ASCII UID encoding changed');
$newOwner=ipskOpenLdapOwner($uniqueSource,'bob');
foreach(['bob-unique.test','bob-old-label.test'] as $legacyOwner){
    $row=$c->query('SELECT e.createdBy AS owner,a.createdBy AS associationOwner,e.pskValue FROM endpoints e JOIN endpointAssociations a ON a.endpointId=e.id WHERE e.id='.$legacyRows[$legacyOwner])->fetch_assoc();
    verify($row['owner']===$newOwner && $row['associationOwner']===$newOwner && $row['pskValue']==='psk=LegacyKey123','Migration changed keys or failed owner/alias preservation');
}
verify($c->query("SELECT COUNT(*) FROM userSidCache WHERE sid='$newOwner'")->fetch_row()[0]==1,'Alias cache merge failed');
verify($c->query('SELECT createdBy FROM endpoints WHERE id='.$legacyRows['unmapped-deleted-directory'])->fetch_row()[0]==='unmapped-deleted-directory','Unknown legacy owner guessed');
$db->dbSchemaVersion=12;verify($db->runSchemaMigrations(13)['success'],'Owner migration retry failed');
verify($c->query('SELECT COUNT(*) FROM endpoints WHERE createdBy=\''.$newOwner.'\'')->fetch_row()[0]==2,'Retry changed migrated owners');
echo "PASS review 57 integration: source-scoped ownership/PSK/quota, aliases, ambiguous migration denial, rollback, cache merge, key preservation and retry\n";
