<?php

/**
 *@license
 *
 *Copyright 2021 Cisco Systems, Inc. or its affiliates
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 */

	


	
/**
 *@author	Gary Oppel (gaoppel@cisco.com)
 *@author	Hosuk Won (howon@cisco.com)
 *@contributor	Drew Betz (anbetz@cisco.com)
 *@contributor	Nick Ciesinski (nciesins@cisco.com)
 */

	class BaseLDAPInterface {
		
		private $ldapHost;
		private $ldapDomain;
		private $ldapUsername;
		private $ldapPassword;
		private $ldapBaseDN;
		private $ldapsecure = true;
		private $iPSKManagerClass;
		private $sslDisableVerify;
		private $directoryType;
        private $identitySourceId;
		
		function __construct($ldapServer = null, $domainName = null, $username = null, $password = null, $baseDN = null, $ldaps = true, $sslCheck = false, $directoryType = 0, $ipskManagerClass = false, $identitySourceId = null) {		
			$this->ldapHost = $ldapServer;
			$this->ldapDomain = $domainName;
			$this->ldapUsername = $username;
			$this->ldapPassword = $password;
			$this->ldapBaseDN = $baseDN;
			$this->ldapsecure = $ldaps;
			$this->iPSKManagerClass = $ipskManagerClass;
			$this->sslDisableVerify = $sslCheck;
			$this->directoryType = $directoryType;
            $this->identitySourceId = $identitySourceId;
		}
		
		function set_ldapHost($hostname) {
			$this->ldapHost = $hostname;
		}
		
		function get_ldapHost(){
			return $this->ldapHost;
		}
		
		function set_ldapDomain($domain) {
			$this->ldapDomain = $domain;
		}
		
		function get_ldapDomain(){
			return $this->ldapDomain;
		}
		
		function set_Username($username) {
			$this->ldapUsername = $username;
		}
		
		function get_Username(){
			return $this->ldapUsername;
		}
		
		function set_Password($password) {
			$this->ldapPassword = $password;
		}
		
		function set_LDAPSecure($ldaps) {
			$this->ldapsecure = $ldaps;
		}
		
		function set_baseDN($basedn) {
			$this->ldapBaseDN = $basedn;
		}
		
		function set_sslCheck($sslCheck) {
			$this->sslDisableVerify = $sslCheck;
		}

		function get_baseDN(){
			return $this->ldapBaseDN;
		}
		
		function get_LDAPSecure() {
			return $this->ldapsecure;
		}

		function get_sslCheck() {
			return $this->sslDisableVerify;
		}

		function get_directoryType() {
			return $this->directoryType;
		}

		function set_directoryType($directoryType) {
			$this->directoryType = $directoryType;
		}
		
		function testLdapServer(){
			
			if ($this->sslDisableVerify) {
				ldap_set_option(NULL, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_ALLOW);
			}

			if($this->ldapsecure){
				$ldapConnection = ldap_connect("ldaps://".$this->ldapHost);
			}else{
				$ldapConnection = ldap_connect("ldap://".$this->ldapHost);
			}

			ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
			
			$ldapBind = @ldap_bind($ldapConnection, $this->ldapUsername, $this->ldapPassword);
			
			if($ldapBind){
				return true;
			}else{
				return false;
			}
			
		}

        function getGroupsForMember($ldap_conn, $member_dn, $already_seen = []) {
            $visited = array_fill_keys(array_map('strtolower', $already_seen), true);
            $visited[strtolower($member_dn)] = true;
            $queue = [$member_dn];
            $groups = [];
            for ($offset = 0; $offset < count($queue); $offset++) {
                $filter = '(member='.ldap_escape($queue[$offset], '', LDAP_ESCAPE_FILTER).')';
                $result = ldap_search($ldap_conn, $this->ldapBaseDN, $filter, ['dn']);
                if ($result === false) { throw new RuntimeException('Unable to resolve directory groups'); }
                $entries = ldap_get_entries($ldap_conn, $result);
                if ($entries === false) { throw new RuntimeException('Unable to read directory groups'); }
                for ($i = 0; $i < $entries['count']; $i++) {
                    $dn = $entries[$i]['dn'];
                    $key = strtolower($dn);
                    if (!isset($groups[$key])) { $groups[$key] = $dn; }
                    if (isset($visited[$key])) { continue; }
                    // Fail closed rather than authorizing against an incomplete membership set.
                    if (count($visited) >= 10000) { throw new RuntimeException('Directory group traversal limit exceeded'); }
                    $visited[$key] = true;
                    $queue[] = $dn;
                }
            }
            return array_values($groups);
        }

		function authenticateUser($username, $password, $saml = false, $nestedGroup = false){
            ipskResetIdentity();

			if ($this->sslDisableVerify) {
				ldap_set_option(NULL, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_ALLOW);
			}
			
			if($this->ldapsecure){
				$ldapConnection = ldap_connect("ldaps://".$this->ldapHost);
			}else{
				$ldapConnection = ldap_connect("ldap://".$this->ldapHost);
			}

			ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
			
			$ldapBind = @ldap_bind($ldapConnection, $this->ldapUsername, $this->ldapPassword);

			if($ldapBind){

				if($this->directoryType == 0) {
					if(strpos($username,"@")){
						$filter = '(userPrincipalName='.ldap_escape($username, '', LDAP_ESCAPE_FILTER).')';
					}elseif(strpos($username,"\\")){
						$username = substr($username,strpos($username,"\\") + 1);
						$filter = '(sAMAccountName='.ldap_escape($username, '', LDAP_ESCAPE_FILTER).')';
					}else{
						$filter = '(sAMAccountName='.ldap_escape($username, '', LDAP_ESCAPE_FILTER).')';
					}
				
					$attributes = array("name", "mail", "samaccountname", "objectSid", "memberof", "userPrincipalName");
				}

				if($this->directoryType == 1) {
					if(strpos($username,"@")){
						$filter = '(mail='.ldap_escape($username, '', LDAP_ESCAPE_FILTER).')';
					}else{
						$filter = '(uid='.ldap_escape($username, '', LDAP_ESCAPE_FILTER).')';
					}
				
					$attributes = array("sn", "givenName", "mail", "cn", "memberof", "uid");
				}

				$result = ldap_search($ldapConnection, $this->ldapBaseDN, $filter, $attributes);

				$entries = ldap_get_entries($ldapConnection, $result);  

				if($entries['count'] == 1){
					$userDN = $entries[0]['dn'];
					if ($saml == false) {
						$ldapBind = @ldap_bind($ldapConnection, $userDN, $password);
					}
					else {
						$ldapBind == true;
					}

					if($ldapBind){
						if($this->iPSKManagerClass){
							//LOG::Entry
							$logData = $this->iPSKManagerClass->generateLogData(Array("base64SID"=>base64_encode($entries[0]['objectsid'][0] ?? '')));
							$logMessage = "REQUEST:SUCCESS;ACTION:AUTHENTICATE-USER;USERNAME:".$username.";AUTHDIRECTORY:".$this->ldapDomain.";";
							$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						}

						if ($nestedGroup && $this->directoryType == 0) {
							$memberOfGroups = $this->getGroupsForMember($ldapConnection, $userDN);
							$memberOfGroups['count'] = count($memberOfGroups);
							$_SESSION['memberOf'] = $memberOfGroups;
						} else {
							$_SESSION['memberOf'] = $entries[0]['memberof'] ?? ['count'=>0];
						}
						
						if($this->directoryType == 0) {
							$_SESSION['sAMAccountName'] = $entries[0]['samaccountname'][0];
							$_SESSION['userPrincipalName'] = $entries[0]['userprincipalname'][0];
							$_SESSION['fullName'] = (isset($entries[0]['name'][0])) ? $entries[0]['name'][0] : '';
							$_SESSION['emailAddress'] = (isset($entries[0]['mail'][0])) ? $entries[0]['mail'][0] : '';
							$_SESSION['logonUsername'] = $username;
							$_SESSION['logonSID'] = convertBinSID($entries[0]['objectsid'][0]);
							$_SESSION['logonDN'] = $userDN;
							$_SESSION['logonDomain'] = $this->ldapDomain;
							ipskRotateSession();
                            $_SESSION['identitySourceId'] = $this->identitySourceId;
                            $_SESSION['identityFormatVersion'] = 1;
                            $_SESSION['authenticationGranted'] = true;
							$_SESSION['authenticationTimestamp'] = time();
							$_SESSION['logonTime'] = time();
							$_SESSION['loggedIn'] = true;
							if(isset($_SESSION['logoutTimer'])) {
								unset($_SESSION['logoutTimer']);
							}
						}

						if($this->directoryType == 1) {
                            $owner = ipskOpenLdapOwner($this->identitySourceId, $entries[0]['uid'][0] ?? null);
                            if ($owner === false) { ipskResetIdentity(); return false; }
							$_SESSION['sAMAccountName'] = $entries[0]['uid'][0];
							if($entries[0]['mail'][0] != ''){
								$_SESSION['userPrincipalName'] = $entries[0]['mail'][0];
							}else{
								$_SESSION['userPrincipalName'] = $username.'@'.$this->ldapDomain;
							}
							$_SESSION['fullName'] = trim(($entries[0]['givenname'][0] ?? '').' '.($entries[0]['sn'][0] ?? ''));
							$_SESSION['emailAddress'] = (isset($entries[0]['mail'][0])) ? $entries[0]['mail'][0] : '';
							$_SESSION['logonUsername'] = $username;
							$_SESSION['logonSID'] = $owner;
							$_SESSION['logonDN'] = $userDN;
							$_SESSION['logonDomain'] = $this->ldapDomain;
							ipskRotateSession();
                            $_SESSION['identitySourceId'] = $this->identitySourceId;
                            $_SESSION['identityFormatVersion'] = 1;
                            $_SESSION['authenticationGranted'] = true;
							$_SESSION['authenticationTimestamp'] = time();
							$_SESSION['logonTime'] = time();
							$_SESSION['loggedIn'] = true;
							if(isset($_SESSION['logoutTimer'])) {
								unset($_SESSION['logoutTimer']);
							}
						}

						return true;
					}else{
						if($this->iPSKManagerClass){
							//LOG::Entry
							$logData = $this->iPSKManagerClass->generateLogData();
							$logMessage = "REQUEST:FAILURE[ldap_user_authn_failure];ACTION:AUTHENTICATE-USER;USERNAME:".$username.";AUTHDIRECTORY:".$this->ldapDomain.";";
							$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						}
						return false;
					}
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData();
						$logMessage = "REQUEST:FAILURE[ldap_user_lookup_failed];ACTION:AUTHENTICATE-USER;USERNAME:".$username.";AUTHDIRECTORY:".$this->ldapDomain.";";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					return false;
				}
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData();
					$logMessage = "REQUEST:FAILURE[ldap_server_logon_failure];ACTION:AUTHENTICATE-USER;USERNAME:".$username.";AUTHDIRECTORY:".$this->ldapDomain.";";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				return false;
			}
		}
	}
?>
