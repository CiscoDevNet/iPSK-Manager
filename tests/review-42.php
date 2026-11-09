<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/ldap-stubs.php';
$ldap=new BaseLDAPInterface('fixture','fixture','service','service','DC=fixture',false,false,1);
foreach ([['Alice','Example','Alice Example'],['Alice',null,'Alice'],[null,'Example','Example'],[null,null,'']] as [$first,$last,$expected]) {
    $GLOBALS['ldapEntries']=function($filter)use($first,$last){$entry=['dn'=>'uid=alice','uid'=>['alice'],'mail'=>['alice@example.test'],'memberof'=>['count'=>0]];if($first!==null)$entry['givenname']=[$first];if($last!==null)$entry['sn']=[$last];return ['count'=>1,0=>$entry];};
    verify($ldap->authenticateUser('alice','valid'),'Authentication failed');
    verify($_SESSION['fullName']===$expected,'Incorrect OpenLDAP display name');
}
echo "PASS review 42: lowercase LDAP attributes, both/single/absent name components\n";
