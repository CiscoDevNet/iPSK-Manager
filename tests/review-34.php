<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/http-harness.php';require __DIR__.'/ldap-stubs.php';
withHttpFixture(function($base) {
    foreach (["DOMAIN\\alice","o'connor",'alice@example.test'] as $username) {
        $r=postSanitizer($base,['inputUsername'=>$username,'userName'=>$username]);
        verify($r['inputUsername']===$username && $r['userName']===$username,'Username silently rewritten');
    }
    verify(postSanitizer($base,['inputUsername'=>"alice\0"])['inputUsername']===false,'Invalid username rewritten instead of rejected');
});
$GLOBALS['ldapEntries']=function($filter){return ['count'=>0];};
$ldap=new BaseLDAPInterface('fixture','fixture','service','service','DC=fixture',false,false,0);
$ldap->authenticateUser("DOMAIN\\alice",'valid');
verify(end($GLOBALS['ldapFilters'])==='(sAMAccountName=alice)','Domain qualification not preserved until LDAP handling');
$ldap->authenticateUser('alice*)(uid=*)','valid');
verify(end($GLOBALS['ldapFilters'])==='(sAMAccountName=alice\\2a\\29\\28uid=\\2a\\29)','LDAP filter injection');
echo "PASS review 34: exact HTTP identity preservation and LDAP-boundary filter escaping\n";
