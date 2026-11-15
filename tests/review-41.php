<?php
require __DIR__.'/review-bootstrap.php';require __DIR__.'/ldap-stubs.php';
$ldap=new BaseLDAPInterface();
$graph=['user'=>['a','b'],'a'=>['c','d'],'b'=>['C','d'],'c'=>['a'],'d'=>[]];
$GLOBALS['ldapEntries']=function($filter)use(&$graph){$node=strtolower(substr($filter,8,-1));$dns=$graph[$node]??[];return ['count'=>count($dns)]+array_map(fn($dn)=>['dn'=>$dn],$dns);};
$GLOBALS['ldapFilters']=[];$groups=$ldap->getGroupsForMember('fixture','user');
verify(count($groups)===4 && count($GLOBALS['ldapFilters'])===5,'Diamond/cycle duplicated groups or searches');
$graph=[];for($i=0;$i<2000;$i++){$graph['g'.$i]=['g'.($i+1)];}
$GLOBALS['ldapFilters']=[];$groups=$ldap->getGroupsForMember('fixture','g0');
verify(count($groups)===2000 && count($GLOBALS['ldapFilters'])===2001,'Deep traversal incomplete');
for($i=2000;$i<10001;$i++){$graph['g'.$i]=['g'.($i+1)];}
try{$ldap->getGroupsForMember('fixture','g0');throw new Exception('Traversal limit ignored');}catch(RuntimeException $e){verify(str_contains($e->getMessage(),'limit'),'Wrong failure');}
echo "PASS review 41: unique shared ancestors/cycles, deep iterative traversal and fail-closed work bound\n";
