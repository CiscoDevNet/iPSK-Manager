<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/ldap-stubs.php';
$GLOBALS['ldapEntries']=fn()=>['count'=>1,0=>['dn'=>'uid=alice,dc=fixture','uid'=>['alice'],'mail'=>['alice@example.test'],'givenname'=>['Alice'],'sn'=>['Fixture'],'memberof'=>['count'=>1,'cn=Users,dc=fixture']]];
$GLOBALS['ldapBind']=fn($connection,$dn,$password)=>$dn==='service'?$password==='service':$password===($connection==='ldap://independent'?'second-password':'first-password');
$owners=[];
foreach([['primary','same-domain',1,'first-password'],['independent','same-domain',2,'second-password'],['alias','other-label',1,'first-password']] as [$server,$domain,$source,$password]){
    $_SESSION=[];$ldap=new BaseLDAPInterface($server,$domain,'service','service','dc=fixture',false,false,1,false,$source);
    verify($ldap->authenticateUser('alice',$password),'Fixture authentication failed');
    $_SESSION['authorizationGranted']=true;
    verify(ipskLoginSessionCheck(),'New scoped session rejected');$owners[]=$_SESSION['logonSID'];
}
verify($owners[0]!==$owners[1] && $owners[0]===$owners[2],'Source separation or deliberate alias failed');
verify($owners[0]===ipskOpenLdapOwner(1,'alice'),'Owner encoding inconsistent');
$ldap=new BaseLDAPInterface('primary','same-domain','service','service','dc=fixture',false,false,1);
verify(!$ldap->authenticateUser('alice','first-password'),'Unbound source accepted');
$_SESSION=validSession();$_SESSION['identitySourceId']=1;$_SESSION['logonSID']='alice-same-domain';$_SESSION['adminAuthorized']=true;
verify(!ipskLoginSessionCheck() && !ipskAdminSessionCheck(),'Legacy external session retained unscoped authority');
$_SESSION=validSession();verify(ipskLoginSessionCheck(),'Internal recovery login affected');
foreach([null,0,-1,'1'] as $source)verify(ipskOpenLdapOwner($source,'alice')===false,'Invalid source accepted');
echo "PASS review 57: independent OpenLDAP owners, deliberate aliases, invalid-source denial and stale-session rejection\n";
