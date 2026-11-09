<?php
// This fixture models directory transport; it never contacts an LDAP server.
define('LDAP_OPT_PROTOCOL_VERSION',17);define('LDAP_OPT_REFERRALS',8);define('LDAP_ESCAPE_FILTER',1);
function ldap_connect($uri) {return $uri;}
function ldap_set_option(...$args) {return true;}
function ldap_bind($connection,$dn,$password) {return in_array($password,['service','valid'],true);}
function ldap_escape($value,$ignore='',$flags=0) {return strtr($value,["\\"=>'\\5c','*'=>'\\2a','('=>'\\28',')'=>'\\29',"\0"=>'\\00']);}
function ldap_search($connection,$base,$filter,$attributes) {$GLOBALS['ldapFilters'][]=$filter;return $filter;}
function ldap_get_entries($connection,$filter) {return ($GLOBALS['ldapEntries'])($filter);}
require __DIR__.'/../supportfiles/include/BaseLDAPClass.php';
