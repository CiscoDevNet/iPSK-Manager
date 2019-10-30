<?php
	
/**
 *@license
 *Copyright (c) 2019 Cisco and/or its affiliates.
 *
 *This software is licensed to you under the terms of the Cisco Sample
 *Code License, Version 1.1 (the "License"). You may obtain a copy of the
 *License at
 *
 *			   https://developer.cisco.com/docs/licenses
 *
 *All use of the material herein must be in accordance with the terms of
 *the License. All rights not expressly granted by the License are
 *reserved. Unless required by applicable law or agreed to separately in
 *writing, software distributed under the License is distributed on an "AS
 *IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 *or implied.
 */

	


	
/**
 *@author	Gary Oppel (gaoppel@cisco.com)
 *@author	Hosuk Won (howon@cisco.com)
 *@contributor	Drew Betz (anbetz@cisco.com)
 */
	
	class iPSKManagerDatabase {
	
		public $requiredSchemaVersion = 1;
		public $platformClassVersion = 1;
		public $lastFuncModVersion = 1;
		public $systemConfigured;
		public $dbSchemaVersion;
		public $loggingLevel;
		public $passwordComplexity;
		
		private $dbHostname;
		private $dbUsername;
		private $dbPassword;
		private $dbDatabase;
		private $encryptionKey;
		
		private $dbConnection;
	
		function __construct($hostname = null, $username = null, $password = null, $database = null) {		
			$this->dbHostname = $hostname;
			$this->dbUsername = $username;
			$this->dbPassword = $password;
			$this->dbDatabase = $database;
			
			if($hostname != null && $username != null && $password != null && $database != null){
				$this->dbConnection = new mysqli($hostname, $username, $password, $database);
				
				if ($this->dbConnection->connect_error) {
					die('Connect Error (' . $this->dbConnection->connect_errno . ') '  . $this->dbConnection->connect_error);
				}
				
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'platform-config' AND `keyName` = 'system-configured' LIMIT 1";
				
				$queryResult = $this->dbConnection->query($query);
			
				if($queryResult){
					$row = $queryResult->fetch_assoc();
					
					$this->systemConfigured = $row["value"];
				}
				
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'db-schema' AND `keyName` = 'version' LIMIT 1";
				
				$queryResult = $this->dbConnection->query($query);
			
				if($queryResult){
					$row = $queryResult->fetch_assoc();
					
					$this->dbSchemaVersion = $row["value"];
				}
				
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'platform-config' AND `keyName` = 'logging-level' LIMIT 1";
				
				$queryResult = $this->dbConnection->query($query);
			
				if($queryResult){
					$row = $queryResult->fetch_assoc();
					
					$this->loggingLevel = $row["value"];
				}else{
					$this->loggingLevel = 0;
				}
				
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'advanced-settings' AND `keyName` = 'password-complexity' LIMIT 1";
				
				$queryResult = $this->dbConnection->query($query);
			
				if($queryResult){
					if($queryResult->num_rows > 0){
						$row = $queryResult->fetch_assoc();
						
						$this->passwordComplexity = $row["value"];
						
					}else{
						$this->passwordComplexity = 15;
					}
				}else{
					$this->passwordComplexity = 15;
				}
				
				//OPTIONAL: Set Server Environment Variables to relocate the Encryption Key.
				if(!isset($this->encryptionKey)){
					if(isset($_SERVER['ENC_KEY'])){
						$this->encryptionKey = ($_SERVER['ENC_KEY'] != "") ? $_SERVER['ENC_KEY'] : '';
					}
				}
			}
		}
	
		function set_dbHostname($hostname) {
			$this->dbHostname = $hostname;
		}
		
		function get_dbHostname(){
			return $this->dbHostname;
		}
		
		function set_dbUsername($username) {
			$this->dbUsername = $username;
		}
		
		function get_dbUsername(){
			return $this->dbUsername;
		}
		
		function set_dbPassword($password) {
			$this->dbPassword = $password;
		}
		
		function set_dbDatabase($database) {
			$this->dbDatabase = $database;
		}
		
		function get_dbDatabase(){
			return $this->dbDatabase;
		}
		
		function get_dbSchemaVersion(){
			return $this->dbSchemaVersion;
		}
		
		function set_encryptionKey($key){
			if(!$this->encryptionKey){
				$this->encryptionKey = $key;
			}
		}

		function generateRandomPassword($length = 8, $complexity = null){
			//Define the Alphabet Array for Selectable Complexity
			$alphabet = '';
			$alphabetArray = Array(1=>"abcdefghijkmnopqrstuvwxyz", 2=>"ABCDEFGHJKLMNPQRSTUVWXYZ", 4=>"123456789", 8=>'!?#$%@*()',16=>'lIO0');
			
			//Check if user defined Complexity to overwrite global setting & if complexity has been defined (not 0)
			if($complexity == null){
				if($this->passwordComplexity == 0){
					$complexity = 15;
				}else{
					$complexity = $this->passwordComplexity;
				}
			}
			
			//Generate Alphabet String based on user defined Complexity
			foreach($alphabetArray as $index => $entry){
				if($complexity & $index){
					$alphabet .= $entry;
				}
			}
			
			//Limit the maximum length to 64 Characters
			if($length > 64){ $length = 64;}
			
			$generatedPsk = "";
			//Loop through and select random characters from the alphabet
			for($char = 0; $char < $length; $char++){
				$generatedPsk .= substr($alphabet, random_int(0,strlen($alphabet)) - 1, 1);
			}

			return $generatedPsk;
		}
		
		function checkGlobalSettingCount($settingClass, $settingName, $settingIndex = 0){
			
			$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = '".$settingClass."' AND `keyName` = '".$settingName."' AND `optionIndex` = '".$settingIndex."'";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return $queryResult->num_rows;
			}else{
				return false;
			}
		}

		function getGlobalClassSetting($className){
			$query = "SELECT * from settings WHERE settingClass='".$className."' AND page='global' AND encrypted='0'";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					while($row = $queryResult->fetch_assoc()){
						$result[$row['keyName']] = $row['value'];
					}
					return $result;
				}else{
					return false;
				}	
			}else{
				return false;
			}
		}
			
		function getGlobalSetting($settingClass, $settingName, $settingIndex = 0){
			
			$query = "SELECT `value`, `encrypted` FROM `settings` WHERE `page` = 'global' AND `settingClass` = '".$settingClass."' AND `keyName` = '".$settingName."' AND `optionIndex` = '".$settingIndex."' LIMIT 1";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					
					if($row['encrypted'] == '1'){
						return "***Encrypted Content***";
					}else{
						return $row["value"];
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}	
		
		function setGlobalSetting($settingClass, $settingName, $value, $encryptedField = 0, $settingIndex = 0){
			
			$settingCheck = $this->checkGlobalSettingCount($settingClass, $settingName, $settingIndex);
			
			if($settingCheck > 0){
			
				$queryUpdate = sprintf("UPDATE `settings` SET `value` = '%s',`encrypted` = '%d' WHERE `page` = 'global' AND `settingClass` = '%s' AND `keyName` = '%s' AND `optionIndex` = %d", $this->dbConnection->real_escape_string($value), $this->dbConnection->real_escape_string($encryptedField), $this->dbConnection->real_escape_string($settingClass), $this->dbConnection->real_escape_string($settingName), $this->dbConnection->real_escape_string($settingIndex));
			
				$queryResult = $this->dbConnection->query($queryUpdate);

				if($queryResult){
					return true;
				}else{
					return false;
				}

			}else{	
				$queryInsert = sprintf("INSERT INTO `settings` (`value`, `page`, `settingClass`, `keyName`, `optionIndex`,`encrypted`) VALUES('%s','global','%s','%s',%d,%d)", $this->dbConnection->real_escape_string($value), $this->dbConnection->real_escape_string($settingClass), $this->dbConnection->real_escape_string($settingName), $this->dbConnection->real_escape_string($settingIndex), $this->dbConnection->real_escape_string($encryptedField));
				
				$queryResult = $this->dbConnection->query($queryInsert);
				
				if($queryResult){
					return true;
				}else{
					return false;
				}
			}
		}

		function setISEERSPassword($password){
			
			$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

			$ciphertext = sodium_crypto_secretbox($password, $nonce, base64_decode($this->encryptionKey));

			$encryptedPassword = base64_encode($nonce . $ciphertext);
			
			if($this->setGlobalSetting("ise-ers-credentials","ersPassword", $encryptedPassword, 1)){
				return true;
			}else{
				return false;
			}
		}	
	
		function setISEMnTPassword($password){
			
			$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

			$ciphertext = sodium_crypto_secretbox($password, $nonce, base64_decode($this->encryptionKey));

			$encryptedPassword = base64_encode($nonce . $ciphertext);
			
			if($this->setGlobalSetting("ise-mnt-credentials","mntPassword", $encryptedPassword, 1)){
				return true;
			}else{
				return false;
			}
		}

		function getSmtpSettings(){
			if($this->encryptionKey != ""){
				$query = "SELECT `keyName`, `value`, `encrypted` FROM `settings` WHERE `page`='global' AND `settingClass`='smtp-settings'";
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						while($row = $queryResult->fetch_assoc()){
							
							if($row['encrypted'] == true){				
								if($row['value'] != ""){
									$decoded = base64_decode($row['value']);
								
									$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
									$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
									$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, base64_decode($this->encryptionKey));
									
									$result[$row['keyName']] = $plaintext;
								}else{
									$result[$row['keyName']] = '';
								}
							}else{
								$result[$row['keyName']] = $row['value'];
							}
						}
						
						return $result;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function setSMTPPassword($password){
			
			$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

			$ciphertext = sodium_crypto_secretbox($password, $nonce, base64_decode($this->encryptionKey));

			$encryptedPassword = base64_encode($nonce . $ciphertext);
			
			if($this->setGlobalSetting("smtp-settings","smtp-password", $encryptedPassword, 1)){
				return true;
			}else{
				return false;
			}
		}

		function getPortalAdminGroups(){
			$count = 0;
			
			$query = "SELECT `groupDn` FROM `internalGroups` WHERE `permissions` = '1'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					while($row = $queryResult->fetch_assoc()){
						$result[$count] = $row['groupDn'];
						$count++;			
					}
					
					$result['count'] = $count;
					
					return $result;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getInternalGroups($groupType = null){
			
			if(is_null($groupType)){
				$query = "SELECT `id`, `groupName`, `description`, `groupType`, `groupDn`, `permissions`, `visible`, `createdBy`, `createdDate` FROM `internalGroups`";
			}else{
				$query = "SELECT `id`, `groupName`, `description`, `groupType`, `groupDn`, `permissions`, `visible`, `createdBy`, `createdDate` FROM `internalGroups` WHERE groupType = '$groupType'";
			}
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getInternalGroupById($groupId){
			
			$query = "SELECT `id`, `groupName`, `description`, `groupType`, `groupDn`, `permissions`, `visible`, `createdBy`, `createdDate` FROM `internalGroups` WHERE id = '$groupId'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getInternalUserGroupMembership($userId){

			$query = sprintf("SELECT `internalUserGroupMapping`.`userId`,`internalUserGroupMapping`.`groupId`, `internalGroups`.`id`, `internalGroups`.`groupName`, `internalGroups`.`groupDn` FROM `internalUserGroupMapping` INNER JOIN `internalGroups` ON internalUserGroupMapping.groupId =  `internalGroups`.`id` WHERE `internalUserGroupMapping`.`userId` = '%s'", $this->dbConnection->real_escape_string($userId));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getInternalUser($username){

			$query = sprintf("SELECT `id`,`userName`,`fullName`,`description`,`createdBy`,`createdDate` FROM `internalUsers` WHERE userName = '%s' LIMIT 1", $this->dbConnection->real_escape_string($username));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getInternalUsers(){
			
			$query = "SELECT `id`, `userName`, `description`, `fullName` FROM `internalUsers`";

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getInternalUserById($userId){
			
			$query = "SELECT `id`, `userName`, `description`, `fullName`, `email`, `dn`, `sid`, `enabled`, `createdBy`, `createdDate` FROM `internalUsers` WHERE id = '$userId'";

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function authenticateInternalUser($username,$password){
			
			$authQuery = sprintf("SELECT password FROM `internalUsers` WHERE userName = '%s' LIMIT 1", $this->dbConnection->real_escape_string($username));
			
			$authQueryResult = $this->dbConnection->query($authQuery);
			
			if($authQueryResult){
				if($authQueryResult->num_rows > 0){
					$user = $authQueryResult->fetch_assoc();
					
					if(password_verify($password,$user['password'])){
						
						$userQuery = sprintf("SELECT `id`,`userName`,`fullName`,`description`, `email`, `dn`,`sid`,`createdBy`,`createdDate` FROM `internalUsers` WHERE userName = '%s' LIMIT 1", $this->dbConnection->real_escape_string($username));
			
						$userQueryResult = $this->dbConnection->query($userQuery);
						
						$user = $userQueryResult->fetch_assoc();
					

						$membershipQuery = "SELECT `internalUserGroupMapping`.`userId`,`internalUserGroupMapping`.`groupId`, `internalGroups`.`id`, `internalGroups`.`groupName`, `internalGroups`.`groupDn` FROM `internalUserGroupMapping` INNER JOIN `internalGroups` ON internalUserGroupMapping.groupId =  `internalGroups`.`id` WHERE `internalUserGroupMapping`.`userId` = '".$user['id']."'";

						$groupMembership = $this->dbConnection->query($membershipQuery);
						
						if($groupMembership->num_rows > 0){
							$count = 0;
							
							while($row = $groupMembership->fetch_assoc()){
								$memberOf[$count] = $row['groupDn'];
								$count++;
							}
							$memberOf['count'] = $count;
						}
						
						$_SESSION['memberOf'] = $memberOf;
						$_SESSION['sAMAccountName'] = $username;
						$_SESSION['userPrincipalName'] = $username."@System.Local";
						$_SESSION['fullName'] = $user['fullName'];
						$_SESSION['emailAddress'] = $user['email'];
						$_SESSION['logonUsername'] = $username;
						$_SESSION['logonSID'] = $user['sid'];
						$_SESSION['logonDN'] = $user['dn'];
						$_SESSION['logonDomain'] = "System.Local";
						$_SESSION['authenticationGranted'] = true;
						$_SESSION['authenticationTimestamp'] = time();
						$_SESSION['logonTime'] = time();
						$_SESSION['loggedIn'] = true;
						
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}	
			}else{
				return false;
			}
		}

		function getHostnameList(){
			$query = "SELECT * FROM portalHostnames WHERE enabled = '1' AND visible = '1'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getTcpPortListById($id){
			$query = "SELECT `id`, `portalPort`, `portalSecure` FROM `portalPorts` WHERE enabled = '1' AND visible = '1' AND `id` = '$id'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}				
			}else{
				return false;
			}
		}
		
		function getTcpPortList(){
			$query = "SELECT `id`, `portalPort`, `portalSecure` FROM `portalPorts` WHERE enabled = '1' AND visible = '1'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getAuthDirectoryNames(){
			$query = "SELECT id, adConnectionName FROM `ldapServers`";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$result[0] = "Internal";				
					
					while($row = $queryResult->fetch_assoc()){
						$result[$row['id']] = $row['adConnectionName'];
					}
					return $result;
				}else{
					$result[0] = "Internal";
					return $result;
				}
			}else{
				return false;
			}
		}
	
		function getLdapDirectoryCount(){
			$query = "SELECT COUNT(id) as ldapCount FROM `ldapServers`";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row['ldapCount'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getLdapDirectoryListing(){
			$query = "SELECT id, adConnectionName FROM `ldapServers`";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getLdapDirectories(){
			$query = "SELECT id, adConnectionName, adServer, adDomain, adUsername, adPassword, adBaseDN, adSecure, createdBy, createdDate FROM `ldapServers`";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getLdapDirectoryById($directoryId){
			$query = "SELECT id, adConnectionName, adServer, adDomain, adUsername, adPassword, adBaseDN, adSecure, createdBy, createdDate FROM `ldapServers` WHERE id = '$directoryId'";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getLdapSettings($ldapID){
			
			$query = "SELECT adConnectionName, adServer, adDomain, adUsername, adPassword, adBaseDN, adSecure FROM `ldapServers` WHERE id = '$ldapID' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($this->encryptionKey != ''){
				if($queryResult){
					if($queryResult->num_rows > 0){
						$ldapServer = $queryResult->fetch_assoc();
						
						$result['adConnectionName'] = $ldapServer['adConnectionName'];
						$result['adDomain'] = $ldapServer['adDomain'];
						$result['adServer'] = $ldapServer['adServer'];
						$result['adUsername'] = $ldapServer['adUsername'];
						$result['adBaseDN'] = $ldapServer['adBaseDN'];
						$result['adSecure'] = $ldapServer['adSecure'];
						
						$decoded = base64_decode($ldapServer['adPassword']);
						
						$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
						$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
						$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, base64_decode($this->encryptionKey));
						
						$result['adPassword'] = $plaintext;

						return $result;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	
		function getUserPrincipalNameFromCache($sid){
			$query = "SELECT `id`, `userPrincipalName` FROM `userSidCache` WHERE `sid`='$sid' LIMIT 1";
		
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row['userPrincipalName'];
				}else{
					return $sid;
				}
			}else{
				return $sid;
			}
		}
	
		function getISEMnTSettings($instanceId = 0){
			if($this->encryptionKey != ''){
				$query = "SELECT * from settings WHERE settingClass='ise-mnt-credentials' AND page='global' AND optionIndex='".$instanceId."'";
			
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					if($queryResult->num_rows > 0){	
						while($row = $queryResult->fetch_assoc()){
							if($row['value'] != ""){
								if($row['encrypted'] == true){
									
									$decoded = base64_decode($row['value']);
									
									$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
									$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
									$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, base64_decode($this->encryptionKey));
										
										$result[$row['keyName']] = $plaintext;
									}else{
										$result[$row['keyName']] = $row['value'];
									}
								}else{
									$result[$row['keyName']] = $row['value'];
								}
						}
						return $result;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getISEERSSettings($instanceId = 0){
			if($this->encryptionKey != ""){
				$query = "SELECT * from settings WHERE settingClass='ise-ers-credentials' AND page='global' AND optionIndex='".$instanceId."'";
			
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					if($queryResult->num_rows > 0){				
						while($row = $queryResult->fetch_assoc()){
							if($row['value'] != ""){
								if($row['encrypted'] == true){
									
									$decoded = base64_decode($row['value']);
									
									$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
									$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
									$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, base64_decode($this->encryptionKey));
									
									$result[$row['keyName']] = $plaintext;
								}else{
									$result[$row['keyName']] = $row['value'];
								}
							}else{
								$result[$row['keyName']] = $row['value'];
							}	
						}
						return $result;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getUserEndpointCount($endpointGroupId, $sid){
			
			if(isset($endpointGroupId) && isset($sid)){
				
				$query = "SELECT endpoints.createdBy, COUNT(endpointAssociations.epGroupId) as deviceCount FROM endpointAssociations INNER JOIN endpoints ON endpointAssociations.endpointId = endpoints.id WHERE endpointAssociations.epGroupId = '$endpointGroupId' AND endpoints.createdBy = '$sid' GROUP BY endpoints.createdBy";
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						$row = $queryResult->fetch_assoc();
						
						if($row['createdBy'] == $sid){
							return $row['deviceCount'];
						}else{
							return false;
						}
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
			
		}
		
		function getUserPreSharedKey($endpointGroupId, $sid){
			
			if(isset($endpointGroupId) && isset($sid)){
				
				$query = "SELECT endpoints.createdBy, COUNT(endpointAssociations.epGroupId) as deviceCount, endpoints.pskValue FROM endpointAssociations INNER JOIN endpoints ON endpointAssociations.endpointId = endpoints.id WHERE endpointAssociations.epGroupId = '$endpointGroupId' AND endpoints.createdBy = '$sid' GROUP BY endpoints.createdBy, endpoints.pskValue";
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						$row = $queryResult->fetch_assoc();
						
						if($row['createdBy'] == $sid){
							return substr($row['pskValue'],4);
						}else{
							return false;
						}
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
			
		}
		
		function getWirelessNetworks(){
			$query = "SELECT * from wirelessNetworks WHERE visible = 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function emailEndpointGroup($endpointGroupId){
			$query = "SELECT endpointGroups.id, endpointGroups.groupName, endpointGroups.notificationPermission FROM endpointGroups WHERE endpointGroups.id = '$endpointGroupId' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult->num_rows > 0){
				$row = $queryResult->fetch_assoc();
				
				if($row['notificationPermission'] & 1){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}		
		
		function getEndpointGroups(){
			$query = "SELECT `id`,`groupName`,`description`, `enabled`, `authzTemplateId`, `visible`, `notificationPermission`, `createdBy`, `createdDate` FROM `endpointGroups` WHERE visible=1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getEndpointGroupsAndAuthz(){
			$query = "SELECT endpointGroups.id, endpointGroups.groupName, endpointGroups.authzTemplateId, endpointGroups.description, endpointGroups.enabled, endpointGroups.visible, endpointGroups.notificationPermission, endpointGroups.parentSite, authorizationTemplates.authzPolicyName, authorizationTemplates.authzPolicyDescription, authorizationTemplates.ciscoAVPairPSKMode, authorizationTemplates.ciscoAVPairPSK, authorizationTemplates.termLengthSeconds, endpointGroups.createdBy, endpointGroups.createdDate FROM endpointGroups INNER JOIN authorizationTemplates ON authorizationTemplates.id = endpointGroups.authzTemplateId";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getEndpointGroupById($endpointGroupId){
			$query = "SELECT endpointGroups.id, endpointGroups.groupName, endpointGroups.authzTemplateId, endpointGroups.description, endpointGroups.enabled, endpointGroups.visible, endpointGroups.notificationPermission, endpointGroups.parentSite, authorizationTemplates.authzPolicyName, authorizationTemplates.authzPolicyDescription, authorizationTemplates.ciscoAVPairPSKMode, authorizationTemplates.ciscoAVPairPSK, authorizationTemplates.termLengthSeconds, endpointGroups.createdBy, endpointGroups.createdDate FROM endpointGroups INNER JOIN authorizationTemplates ON authorizationTemplates.id = endpointGroups.authzTemplateId WHERE endpointGroups.id = '$endpointGroupId' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getEndpointGroupByPortalId($portalId){
			$query = "SELECT endpointGroups.id,endpointGroups.groupName FROM `endpointGroups` INNER JOIN sponsorGroupEPGMapping ON endpointGroups.id = sponsorGroupEPGMapping.endpointGroupId INNER JOIN sponsorGroupPortalMapping ON sponsorGroupEPGMapping.sponsorGroupId = sponsorGroupPortalMapping.sponsorGroupId WHERE endpointGroups.enabled = 1 AND endpointGroups.visible = 1 AND sponsorGroupPortalMapping.sponsorPortalId = '$portalId'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getEndpointGroupListing(){
			$query = "SELECT id,groupName FROM endpointGroups WHERE visible=1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getEndPointGroupAuthorizations($portalId, $authorizationGroups){
			
			if(is_array($authorizationGroups)){
				$searchDns = "";
								
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "'".$authorizationGroups[$count]['groupDn']."',";
				}
				
				$searchDns = substr($searchDns, 0, -1);
				
				$query = "SELECT internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions, sponsorGroupEPGMapping.endpointGroupId, endpointGroups.groupName, sponsorGroups.maxDevices, sponsorGroupPortalMapping.sponsorGroupId, authorizationTemplates.termLengthSeconds, authorizationTemplates.ciscoAVPairPSK FROM sponsorGroupPortalMapping INNER JOIN sponsorGroups ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN internalGroups ON sponsorGroupInternalMapping.internalGroupId = internalGroups.id INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.sponsorGroupId = sponsorGroups.id INNER JOIN endpointGroups ON sponsorGroupEPGMapping.endpointGroupId = endpointGroups.id INNER JOIN authorizationTemplates ON endpointGroups.authzTemplateId = authorizationTemplates.id WHERE sponsorGroupPortalMapping.sponsorPortalId = '$portalId' AND internalGroups.groupDn IN ($searchDns)";
				
				$queryResult = $this->dbConnection->query($query);
				
				$listCount = 0;
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						while($row = $queryResult->fetch_assoc()){
							$queryData[$listCount]['endpointGroupId'] = $row['endpointGroupId'];
							$queryData[$listCount]['sponsorGroupId'] = $row['sponsorGroupId'];
							$queryData[$listCount]['groupName'] = $row['groupName'];
							$queryData[$listCount]['maxDevices'] = $row['maxDevices'];
							$queryData[$listCount]['groupDn'] = $row['groupDn'];
							$queryData[$listCount]['viewPermissions'] = $row['groupPermissions'] & 15;
							$queryData[$listCount]['advancedPermissions'] = $row['groupPermissions'] & 2032;
							$queryData[$listCount]['groupPermissions'] = $row['groupPermissions'] & 4095;				
							$queryData[$listCount]['termLengthSeconds'] = $row['termLengthSeconds'];
							$queryData[$listCount]['ciscoAVPairPSK'] = $row['ciscoAVPairPSK'];
							
							$listCount++;
						}
						$queryData['count'] = $listCount;
						
						return $queryData;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getEndpointByAssociationId($associationId){
			$query = "SELECT authorizationTemplates.termLengthSeconds, endpointAssociations.id, endpointAssociations.epGroupId, endpointAssociations.macAddress, endpoints.fullName, endpoints.id as endpointId, endpoints.description, endpoints.emailAddress, endpoints.pskValue, endpoints.createdBy FROM endpointAssociations INNER JOIN endpointGroups  ON endpointAssociations.epGroupId = endpointGroups.id INNER JOIN authorizationTemplates  ON authorizationTemplates.id = endpointGroups.authzTemplateId INNER JOIN endpoints  ON endpointAssociations.endpointId = endpoints.id WHERE endpointAssociations.id = '$associationId' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getEndpointsByAuthZPolicy($id){
			$query = "SELECT endpoints.id, endpoints.macAddress, endpointGroups.authzTemplateId FROM `endpoints` INNER JOIN endpointAssociations ON endpoints.id = endpointAssociations.endpointId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id WHERE endpointGroups.authzTemplateId = '$id'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$endpointList['count'] = 0;
					$itemCount = 0;
					
					while($row = $queryResult->fetch_assoc()){
						$endpointList[$itemCount]['id'] = $row['id'];
						$endpointList[$itemCount]['macAddress'] = $row['macAddress'];
						$endpointList[$itemCount]['authzTemplateId '] = $row['authzTemplateId'];
						
						$itemCount++;
					}
					
					$endpointList['count'] = $itemCount;
					
					return $endpointList;
				}else{
					return false;
				}
			}else{
				return false;
			}
				
		}
		
		function getEndPointAssociations(){
			$query = "SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, endpoints.macAddress, endpoints.createdBy, endpointGroups.groupName as epGroupName, endpoints.accountEnabled, endpoints.expirationDate, endpointAssociations.createdDate FROM endpointAssociations INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId ORDER BY endpoints.macAddress ASC";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$listCount = 0;
					
					while($row = $queryResult->fetch_assoc()){
						$rawAssociationList[$listCount]['id'] = $row['id'];
						$rawAssociationList[$listCount]['endpointId'] = $row['endpointId'];
						$rawAssociationList[$listCount]['epGroupId'] = $row['epGroupId'];
						$rawAssociationList[$listCount]['macAddress'] = $row['macAddress'];
						$rawAssociationList[$listCount]['createdBy'] = $row['createdBy'];
						$rawAssociationList[$listCount]['expirationDate'] = $row['expirationDate'];
						$rawAssociationList[$listCount]['accountEnabled'] = $row['accountEnabled'];
						$rawAssociationList[$listCount]['createdDate'] = $row['createdDate'];
						$rawAssociationList[$listCount]['groupName'] = $row['epGroupName'];
						
						$listCount++;
					}
					
					$rawAssociationList['count'] = $listCount;					
					
					return $rawAssociationList;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getEndPointAssociationList($authorizationGroups, $sponsorPortalId = 0, $viewAll = false, $viewallDn = ""){
			$searchDns = "";
			
			if($sponsorPortalId == 0){
				return false;
			}
			
			if(is_array($authorizationGroups)){
				$searchDns = "";
				
				
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "'".$authorizationGroups[$count]['groupDn']."',";
				}
				
				$searchDns = substr($searchDns, 0, -1);
				
				if($viewAll == true){
					$query = "SELECT id, endpointId, epGroupId, sponsorGroupId, macAddress, createdBy, expirationDate, accountEnabled, accountExpired, pskValue, fullName, description, emailAddress, createdDate, groupDn, groupPermissions, groupName FROM (SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, sponsorGroupEPGMapping.sponsorGroupId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions, endpointGroups.groupName FROM endpoints INNER JOIN endpointAssociations ON endpoints.id = endpointAssociations.endpointId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id WHERE internalGroups.groupDn IN ($searchDns) AND sponsorGroupEPGMapping.sponsorGroupId IN (SELECT sponsorGroupId FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = '$sponsorPortalId') UNION SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, '0' as sponsorGroupId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, '$viewallDn' as groupDn, '4' as groupPermissions, endpointGroups.groupName FROM endpointAssociations INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id GROUP BY endpointAssociations.id, endpointAssociations.endpointId) AS completeEndpointList ORDER BY macAddress ASC";
				}else{
					$query = "SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions, endpointGroups.groupName FROM endpoints INNER JOIN endpointAssociations ON endpoints.id = endpointAssociations.endpointId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id WHERE internalGroups.groupDn IN ($searchDns) AND sponsorGroupEPGMapping.sponsorGroupId IN (SELECT sponsorGroupId FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = '$sponsorPortalId') ORDER BY endpointAssociations.macAddress ASC";
				}
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						while($row = $queryResult->fetch_assoc()){
							
							if(isset($associationSeen[$row['macAddress']])){
								$rawAssociationList[$row['macAddress']]['viewPermissions'] = $rawAssociationList[$row['macAddress']]['viewPermissions'] | ($row['groupPermissions'] & 15);
								$rawAssociationList[$row['macAddress']]['advancedPermissions'] = $rawAssociationList[$row['macAddress']]['advancedPermissions'] | ($row['groupPermissions'] & 2032);
								$rawAssociationList[$row['macAddress']]['groupPermissions'] = $rawAssociationList[$row['macAddress']]['groupPermissions'] | ($row['groupPermissions'] & 4095);
							}else{
								$rawAssociationList[$row['macAddress']]['id'] = $row['id'];
								$rawAssociationList[$row['macAddress']]['endpointId'] = $row['endpointId'];
								$rawAssociationList[$row['macAddress']]['epGroupId'] = $row['epGroupId'];
								$rawAssociationList[$row['macAddress']]['macAddress'] = $row['macAddress'];
								$rawAssociationList[$row['macAddress']]['createdBy'] = $row['createdBy'];
								$rawAssociationList[$row['macAddress']]['expirationDate'] = $row['expirationDate'];
								$rawAssociationList[$row['macAddress']]['accountEnabled'] = $row['accountEnabled'];
								$rawAssociationList[$row['macAddress']]['accountExpired'] = $row['accountExpired'];
								$rawAssociationList[$row['macAddress']]['pskValue'] = $row['pskValue'];
								$rawAssociationList[$row['macAddress']]['fullName'] = $row['fullName'];
								$rawAssociationList[$row['macAddress']]['description'] = $row['description'];
								$rawAssociationList[$row['macAddress']]['emailAddress'] = $row['emailAddress'];
								$rawAssociationList[$row['macAddress']]['createdDate'] = $row['createdDate'];
								$rawAssociationList[$row['macAddress']]['viewPermissions'] = $row['groupPermissions'] & 15;
								$rawAssociationList[$row['macAddress']]['advancedPermissions'] = $row['groupPermissions'] & 2032;
								$rawAssociationList[$row['macAddress']]['groupPermissions'] = $row['groupPermissions'] & 4095;
								$rawAssociationList[$row['macAddress']]['groupName'] = $row['groupName'];
								$associationSeen[$row['macAddress']] = true;
							}
						}
						
						$listCount = 0;
						
						foreach($rawAssociationList as $endPoint){
							$mergedAssociationList[$listCount]['id'] = $endPoint['id'];
							$mergedAssociationList[$listCount]['endpointId'] = $endPoint['endpointId'];
							$mergedAssociationList[$listCount]['epGroupId'] = $endPoint['epGroupId'];
							$mergedAssociationList[$listCount]['macAddress'] = $endPoint['macAddress'];
							$mergedAssociationList[$listCount]['createdBy'] = $endPoint['createdBy'];
							$mergedAssociationList[$listCount]['expirationDate'] = $endPoint['expirationDate'];
							$mergedAssociationList[$listCount]['accountEnabled'] = $endPoint['accountEnabled'];
							$mergedAssociationList[$listCount]['accountExpired'] = $endPoint['accountExpired'];
							$mergedAssociationList[$listCount]['pskValue'] = $endPoint['pskValue'];
							$mergedAssociationList[$listCount]['fullName'] = $endPoint['fullName'];
							$mergedAssociationList[$listCount]['description'] = $endPoint['description'];
							$mergedAssociationList[$listCount]['emailAddress'] = $endPoint['emailAddress'];
							$mergedAssociationList[$listCount]['createdDate'] = $endPoint['createdDate'];
							$mergedAssociationList[$listCount]['viewPermissions'] = $endPoint['groupPermissions'] & 15;
							$mergedAssociationList[$listCount]['advancedPermissions'] = $endPoint['groupPermissions'] & 2032;
							$mergedAssociationList[$listCount]['groupPermissions'] = $endPoint['groupPermissions'] & 4095;
							$mergedAssociationList[$listCount]['groupName'] = $endPoint['groupName'];
							
							$listCount++;
						}
						
						$mergedAssociationList['count'] = $listCount;					
						
						return $mergedAssociationList;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getEndPointAssociationPermissions($endpointAssociationId, $authorizationGroups, $sponsorPortalId = 0){
			
			if(is_array($authorizationGroups)){
				$searchDns = "";
				
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "'".$authorizationGroups[$count]['groupDn']."',";
				}
				
				$searchDns = substr($searchDns, 0, -1);
				
				$query = "SELECT id, endpointId, macAddress, createdBy, endpointGroupId, groupName, accountEnabled, expirationDate, accountExpired, pskValue, fullName, description, emailAddress, createdDate, groupDn, groupPermissions FROM (SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.macAddress, endpoints.createdBy, endpoints.lastAccessed, endpointGroups.id as endpointGroupId, endpointGroups.groupName, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, sponsorGroupEPGMapping.sponsorGroupId, sponsorGroupInternalMapping.internalGroupId, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions FROM endpointAssociations INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN sponsorGroupPortalMapping ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId WHERE endpointAssociations.id = '$endpointAssociationId' AND groupDn IN ($searchDns) AND sponsorGroupEPGMapping.sponsorGroupId IN (SELECT sponsorGroupId FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = '$sponsorPortalId')) AS completePermissions GROUP BY id, groupDn, groupPermissions";
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						while($row = $queryResult->fetch_assoc()){
							
							if(isset($associationSeen[$row['macAddress']])){
								$rawEndpointList[$row['macAddress']]['viewPermissions'] =$rawEndpointList[$row['macAddress']]['viewPermissions'] | ($row['groupPermissions'] & 15);
								$rawEndpointList[$row['macAddress']]['advancedPermissions'] = $rawEndpointList[$row['macAddress']]['advancedPermissions'] | ($row['groupPermissions'] & 2032);
								$rawEndpointList[$row['macAddress']]['groupPermissions'] = $rawEndpointList[$row['macAddress']]['groupPermissions'] | ($row['groupPermissions'] & 4095);
							}else{
								$rawEndpointList[$row['macAddress']]['id'] = $row['id'];
								$rawEndpointList[$row['macAddress']]['endpointId'] = $row['endpointId'];
								$rawEndpointList[$row['macAddress']]['macAddress'] = $row['macAddress'];
								$rawEndpointList[$row['macAddress']]['viewPermissions'] = $row['groupPermissions'] & 15;
								$rawEndpointList[$row['macAddress']]['advancedPermissions'] = $row['groupPermissions'] & 2032;
								$rawEndpointList[$row['macAddress']]['groupPermissions'] = $row['groupPermissions'] & 4095;
								$associationSeen[$row['macAddress']] = true;
							}
						}
						
						$listCount = 0;
						
						foreach($rawEndpointList as $endPoint){
							$mergedEndpointList[$listCount]['id'] = $endPoint['id'];
							$mergedEndpointList[$listCount]['endpointId'] = $endPoint['endpointId'];
							$mergedEndpointList[$listCount]['macAddress'] = $endPoint['macAddress'];
							$mergedEndpointList[$listCount]['viewPermissions'] = $endPoint['groupPermissions'] & 15;
							$mergedEndpointList[$listCount]['advancedPermissions'] = $endPoint['groupPermissions'] & 2032;
							$mergedEndpointList[$listCount]['groupPermissions'] = $endPoint['groupPermissions'] & 4095;

							$listCount++;
						}
						
						$mergedEndpointList['count'] = $listCount;					
						
						return $mergedEndpointList;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getEndPointAssociationById($endpointGroupId){
			$query = "SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.createdDate as epCreatedDate, endpointGroups.id as epGroupId, endpointGroups.groupName as epGroupName, endpoints.macAddress, endpoints.expirationDate, endpoints.accountExpired, endpoints.accountEnabled, endpoints.fullName, endpoints.description, endpoints.pskValue, endpoints.lastAccessed, endpoints.emailAddress, endpointAssociations.createdDate FROM endpointAssociations INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId WHERE endpointAssociations.id = '$endpointGroupId' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getAuthorizationTemplates(){
			$query = "SELECT * FROM authorizationTemplates WHERE visible = 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getAuthorizationTemplatesNames(){
			$query = "SELECT id,authzPolicyName FROM authorizationTemplates";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult->num_rows > 0){
				while($row = $queryResult->fetch_assoc()) {
					$groupNames[$row['id']]['authzPolicyName'] = $row['authzPolicyName'];
				}
				
				if($groupNames){
					return $groupNames;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getAuthorizationTemplatesById($templateId){
			$query = "SELECT * FROM authorizationTemplates WHERE id = '$templateId'";
			
			$queryResult = $this->dbConnection->query($query);
		
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getAuthorizationTemplatesbyEPGroupId($id){
			$query = "SELECT authorizationTemplates.ciscoAVPairPSK, authorizationTemplates.termLengthSeconds, authorizationTemplates.pskLength FROM `authorizationTemplates` INNER JOIN endpointGroups ON authorizationTemplates.id = endpointGroups.authzTemplateId WHERE endpointGroups.id = '$id'";
			
			$queryResult = $this->dbConnection->query($query);
		
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}	
		
		function getPortals(){
			$query = "SELECT * FROM sponsorPortals WHERE visible = '1'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getPortalbyHostname($portalFQDN){
			$query = "SELECT * FROM sponsorPortals WHERE portalHostname='$portalFQDN' AND enabled = '1' AND visible = '1' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getPortalByGuid($portalGuid){
			$query = "SELECT sponsorPortals.id, sponsorPortals.portalName, sponsorPortals.description, sponsorPortals.portalId, sponsorPortals.portalType, sponsorPortals.enabled, sponsorPortals.visible, sponsorPortals.portalHostname, sponsorPortals.portalTcpPort, sponsorPortals.enforceTcpPort, sponsorPortals.enforceSecure, sponsorPortals.enforceHostname, sponsorPortals.portalSecure, sponsorPortals.portalTemplate, sponsorPortals.authenticationDirectory, sponsorPortals.createdBy, sponsorPortals.createdDate, sponsorPortalTypes.portalTypeName, sponsorPortalTypes.portalTypeDescription, sponsorPortalTypes.maxSponsorGroups, sponsorPortalTypes.maxEndpointsOverride, sponsorPortalTypes.portalClass, sponsorPortalTypes.portalModule FROM sponsorPortals INNER JOIN sponsorPortalTypes ON sponsorPortalTypes.id = sponsorPortals.portalType WHERE sponsorPortals.portalId='$portalGuid'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getPortalById($id){
			$query = "SELECT sponsorPortals.id, sponsorPortals.portalName, sponsorPortals.description, sponsorPortals.portalId, sponsorPortals.portalType, sponsorPortals.enabled, sponsorPortals.visible, sponsorPortals.portalHostname, sponsorPortals.portalTcpPort, sponsorPortals.enforceTcpPort, sponsorPortals.enforceSecure, sponsorPortals.enforceHostname, sponsorPortals.portalSecure, sponsorPortals.portalTemplate, sponsorPortals.authenticationDirectory, sponsorPortals.createdBy, sponsorPortals.createdDate, sponsorPortalTypes.portalTypeName, sponsorPortalTypes.portalTypeDescription, sponsorPortalTypes.maxSponsorGroups, sponsorPortalTypes.maxEndpointsOverride, sponsorPortalTypes.portalClass, sponsorPortalTypes.portalModule FROM sponsorPortals INNER JOIN sponsorPortalTypes ON sponsorPortalTypes.id = sponsorPortals.portalType WHERE sponsorPortals.id='$id'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getPortalWirelessNetworkAuthorization($portalId, $authorizationGroups){
			
			if(is_array($authorizationGroups)){
				$searchDns = "";
								
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "'".$authorizationGroups[$count]['groupDn']."',";
				}
				
				$searchDns = substr($searchDns, 0, -1);
				
				$query = "SELECT sponsorGroupPortalMapping.sponsorGroupId, sponsorGroupSSIDMapping.wirelessSSIDId, wirelessNetworks.ssidName, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions FROM sponsorGroupPortalMapping INNER JOIN sponsorGroups ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroups.id  INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN internalGroups ON sponsorGroupInternalMapping.internalGroupId = internalGroups.id INNER JOIN sponsorGroupSSIDMapping ON sponsorGroupSSIDMapping.sponsorGroupId = sponsorGroups.id  INNER JOIN wirelessNetworks ON sponsorGroupSSIDMapping.wirelessSSIDId = wirelessNetworks.id WHERE sponsorGroupPortalMapping.sponsorPortalId = '$portalId' AND internalGroups.groupDn IN ($searchDns)";
				
				$queryResult = $this->dbConnection->query($query);
				
				$listCount = 0;
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						while($row = $queryResult->fetch_assoc()){
							$queryData[$listCount]['sponsorGroupId'] = $row['sponsorGroupId'];
							$queryData[$listCount]['wirelessSSIDId'] = $row['wirelessSSIDId'];
							$queryData[$listCount]['ssidName'] = $row['ssidName'];
							$queryData[$listCount]['groupDn'] = $row['groupDn'];
							$queryData[$listCount]['groupPermissions'] = $row['groupPermissions'];
							
							$listCount++;
						}
						$queryData['count'] = $listCount;
						
						return $queryData;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getPortalAuthGroups($portalId){
			$count = 0;
			
			$query = "SELECT internalGroups.groupDn, internalGroups.permissions, sponsorGroupInternalMapping.groupPermissions  FROM `sponsorGroupPortalMapping` INNER JOIN sponsorGroups ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN internalGroups ON sponsorGroupInternalMapping.internalGroupId = internalGroups.id  WHERE sponsorGroupPortalMapping.sponsorPortalId = '$portalId'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					while($row = $queryResult->fetch_assoc()){
						$result[$count]['groupDn'] = $row['groupDn'];
						$result[$count]['permissions'] = $row['permissions'];
						$result[$count]['groupPermissions'] = $row['groupPermissions'];
						$count++;
					}
					
					$result['count'] = $count;
					
					return $result;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getPortalURL($id){
			
			$query = "SELECT sponsorPortals.portalId, sponsorPortals.portalHostname, sponsorPortals.portalTcpPort, sponsorPortals.portalSecure FROM sponsorPortals WHERE sponsorPortals.id='$id' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					
					if($row['portalSecure'] == true){
						$protocol = "https";
					}else{
						$protocol = "http";
					}
					
					$portalUrl = $protocol.'://'.strtolower($row['portalHostname']).':'.$row['portalTcpPort'].'/index.php?portalId='.$row['portalId'];
					
					return $portalUrl;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getSponsorGroupWirelessNetworks($portalId){
			$query = "SELECT sponsorGroupSSIDMapping.id, wirelessNetworks.id as wirelessNetworkId, wirelessNetworks.ssidName FROM `sponsorGroupSSIDMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupSSIDMapping.sponsorGroupId INNER JOIN wirelessNetworks ON wirelessNetworks.id = sponsorGroupSSIDMapping.wirelessSSIDId WHERE sponsorGroupSSIDMapping.sponsorGroupId = '$portalId'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getSponsorGroupInternalGroups($portalId, $full = false){
			
			if($full == true){
				$query = "SELECT sponsorGroupInternalMapping.id, sponsorGroupInternalMapping.groupPermissions, sponsorGroups.id as sponsorGroupId, sponsorGroups.sponsorGroupName, internalGroups.id as internalGroupId, internalGroups.groupName as internalGroupName, internalGroups.groupDn as internalGroupDn FROM `sponsorGroupInternalMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupInternalMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId WHERE sponsorGroupInternalMapping.sponsorGroupId = '$portalId'";
			}else{
				$query = "SELECT sponsorGroupInternalMapping.id, sponsorGroupInternalMapping.groupPermissions, sponsorGroups.id as sponsorGroupId, sponsorGroups.sponsorGroupName, internalGroups.id as internalGroupId, internalGroups.groupName as internalGroupName FROM `sponsorGroupInternalMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupInternalMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId WHERE sponsorGroupInternalMapping.sponsorGroupId = '$portalId'";
			}
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getSponsorPortalTypes(){
			$query = "SELECT `id`, `portalTypeName`, `portalTypeDescription`, `maxSponsorGroups`, `maxEndpointsOverride`, `maxEndpointsAllowed`, `portalClass`, `portalModule` FROM `sponsorPortalTypes`";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getSponsorGroups(){
			$query = "SELECT * FROM sponsorGroups WHERE visible = 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getSponsorGroupById($id){
			$query = "SELECT * FROM sponsorGroups WHERE id='$id'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getSponsorGroupsByPortalId($portalId){
			$query = "SELECT sponsorGroupPortalMapping.id, sponsorGroups.sponsorGroupName, sponsorGroupPortalMapping.sponsorGroupId, sponsorGroupPortalMapping.sponsorPortalId FROM `sponsorGroupPortalMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupPortalMapping.sponsorGroupId WHERE sponsorGroupPortalMapping.sponsorPortalId = '$portalId'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getSponsorGroupEPGroups($id){
			$query = "SELECT endpointGroups.id, endpointGroups.groupName FROM `sponsorGroupEPGMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN endpointGroups ON endpointGroups.id = sponsorGroupEPGMapping.endpointGroupId WHERE sponsorGroupEPGMapping.sponsorGroupId='$id'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function getLogging($minute = 600){
			$query = "SELECT `id`, `dateCreated`, `sessionID`, `fileName`, `functionName`, `className`, `classMethodName`, `lineNumber`, `message` FROM `logging` WHERE `dateCreated` BETWEEN DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL $minute MINUTE) AND CURRENT_TIMESTAMP() ORDER BY `dateCreated` DESC";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getLoggingById($id){
			$query = "SELECT * FROM `logging` WHERE `id`='$id' LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$resultRow = $queryResult->fetch_assoc();
					return $resultRow;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getWirelessNetworkById($wirelessId){
			$query = "SELECT * FROM wirelessNetworks WHERE id = $wirelessId LIMIT 1";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function addEndpoint($macAddress, $fullName, $description, $email, $psk, $expirationDate, $createdBy){
			
			$endpointQuery = sprintf("SELECT id FROM `endpoints` WHERE `macAddress` = '%s'", $this->dbConnection->real_escape_string($macAddress));
			
			$endpointQueryResult = $this->dbConnection->query($endpointQuery);
			
			if($endpointQueryResult->num_rows < 1){
				$query = sprintf("INSERT INTO `endpoints` (`macAddress`, `password`, `fullName`, `description`, `emailAddress`, `pskValue`, `expirationDate`, `createdBy`) VALUES('%s',LCASE(REPLACE(REPLACE('%s',':',''),'-','')),'%s','%s','%s','%s',%d,'%s')", $this->dbConnection->real_escape_string($macAddress), $this->dbConnection->real_escape_string($macAddress), $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $psk, $expirationDate, $this->dbConnection->real_escape_string($createdBy));
			
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					return $this->dbConnection->insert_id;
				}else{
					return false;
				}
			}else{
				$endpoint = $endpointQueryResult->fetch_assoc();
				
				$query = sprintf("UPDATE `endpoints` SET `fullName`='%s', `description`='%s', `emailAddress`='%s', `pskValue`='%s', `expirationDate`='%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $psk, $expirationDate, $this->dbConnection->real_escape_string($endpoint['id']));
			
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					return $endpoint['id'];
				}else{
					return false;
				}
			}
		}
		
		function addBulkEndpoints($macAddress, $fullName, $description, $email, $psk, $expirationDate, $createdBy){
			$multiInput = false;
			
			if(is_array($fullName) && is_array($description) && is_array($email)){
				$multiInput = true;
			}
			
			if(is_array($macAddress)){
				$searchMacAddress = "";
				
				foreach($macAddress as $entry){
					$searchMacAddress .= "'".$entry."',";
				}
					
				$searchMacAddress = substr($searchMacAddress, 0, -1);
				
				$endpointQuery = sprintf("SELECT id, macAddress FROM `endpoints` WHERE `macAddress` IN (%s)", $searchMacAddress);
				
				$endpointQueryResult = $this->dbConnection->query($endpointQuery);
								
				if($endpointQueryResult->num_rows < 1){
					$insertMacAddress = "";
					$macAddressAdd['count'] = 0;
					$macAddressAdd['skipped'] = 0;
					$macAddressAdd['processed'] = 0;
					$count = 0;

					if(count($macAddress) > 0){
						if($multiInput == true){
							foreach($macAddress as $entry => $key){
								$insertMacAddress .= sprintf("('%s',LCASE(REPLACE(REPLACE('%s',':',''),'-','')),'%s','%s','%s','%s',%d,'%s'),", $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($fullName[$entry]), $this->dbConnection->real_escape_string($description[$entry]), $this->dbConnection->real_escape_string($email[$entry]), $psk, $expirationDate, $this->dbConnection->real_escape_string($createdBy));
							}
						}else{
							foreach($macAddress as $entry => $key){
								$insertMacAddress .= sprintf("('%s',LCASE(REPLACE(REPLACE('%s',':',''),'-','')),'%s','%s','%s','%s',%d,'%s'),", $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $psk, $expirationDate, $this->dbConnection->real_escape_string($createdBy));
							}
						}
						
						$insertMacAddress = substr($insertMacAddress, 0, -1);
						
						$bulkQuery = sprintf("INSERT INTO `endpoints` (`macAddress`, `password`, `fullName`, `description`, `emailAddress`, `pskValue`, `expirationDate`, `createdBy`) VALUES%s", $insertMacAddress);
						
						$bulkQueryResult = $this->dbConnection->query($bulkQuery);
						
						if($bulkQueryResult){
							$startInsertId = $this->dbConnection->insert_id;
							$affectRows = $this->dbConnection->affected_rows;
							
							foreach($macAddress as $entry => $key){
								$macAddressAdd[$count]['macAddress'] = $key;
								$macAddressAdd[$count]['exists'] = false;
								$macAddressAdd[$count]['insertId'] = $startInsertId;
								$count++;
								
								$startInsertId++;
							}
							
							$macAddressAdd['count'] = $count;
							$macAddressAdd['processed'] = count($macAddress);
							
							return $macAddressAdd;
						}else{
							return false;
						}
					}else{
						return $macAddressAdd;
					}
					
					if($bulkQueryResult){
						return $this->dbConnection->insert_id;
					}else{
						return false;
					}
				}else{
					$insertMacAddress = "";
					$macAddressAdd['count'] = 0;
					$macAddressAdd['skipped'] = 0;
					$macAddressAdd['processed'] = 0;
					$count = 0;
					
					while($row = $endpointQueryResult->fetch_assoc()){						
						foreach($macAddress as $entry => $key){
							if(strtoupper($row['macAddress']) == strtoupper($key)){
								$macAddressAdd[$count]['macAddress'] = $key;
								$macAddressAdd[$count]['exists'] = true;
								$count++;
								
								unset($macAddress[$entry]);
							}
						}
					}
					
					$macAddressAdd['count'] = $count;
					$macAddressAdd['skipped'] = $count;
					
					if(count($macAddress) > 0){
						if($multiInput == true){
							foreach($macAddress as $entry => $key){
								$insertMacAddress .= sprintf("('%s',LCASE(REPLACE(REPLACE('%s',':',''),'-','')),'%s','%s','%s','%s',%d,'%s'),", $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($fullName[$entry]), $this->dbConnection->real_escape_string($description[$entry]), $this->dbConnection->real_escape_string($email[$entry]), $psk, $expirationDate, $this->dbConnection->real_escape_string($createdBy));
							}
						}else{
							foreach($macAddress as $entry => $key){
								$insertMacAddress .= sprintf("('%s',LCASE(REPLACE(REPLACE('%s',':',''),'-','')),'%s','%s','%s','%s',%d,'%s'),", $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($key), $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $psk, $expirationDate, $this->dbConnection->real_escape_string($createdBy));
							}
						}
						
						$insertMacAddress = substr($insertMacAddress, 0, -1);
						
						$bulkQuery = sprintf("INSERT INTO `endpoints` (`macAddress`, `password`, `fullName`, `description`, `emailAddress`, `pskValue`, `expirationDate`, `createdBy`) VALUES%s", $insertMacAddress);
						
						$bulkQueryResult = $this->dbConnection->query($bulkQuery);
						
						if($bulkQueryResult){
							$startInsertId = $this->dbConnection->insert_id;
							$affectRows = $this->dbConnection->affected_rows;
							
							foreach($macAddress as $entry => $key){
								$macAddressAdd[$count]['macAddress'] = $key;
								$macAddressAdd[$count]['exists'] = false;
								$macAddressAdd[$count]['insertId'] = $startInsertId;
								$count++;
								
								$startInsertId++;
							}
							
							$macAddressAdd['count'] = $count;
							$macAddressAdd['processed'] = count($macAddress);
							
							return $macAddressAdd;
						}else{
							return false;
						}
					}else{
						return $macAddressAdd;
					}
				}
			}else{
				return false;
			}
		}
		
		function addBulkEndpointAssociation($macAddress, $epGroupID, $createdBy){
			
			if(is_array($macAddress)){
				$insertAssociation = "";
				
				for($rowCount = 0; $rowCount < $macAddress['count']; $rowCount++){
					if($macAddress[$rowCount]['exists'] != true){
						$insertAssociation .= sprintf("('%d','%s','%d','%s'),", $this->dbConnection->real_escape_string($macAddress[$rowCount]['insertId']), $this->dbConnection->real_escape_string($macAddress[$rowCount]['macAddress']), $this->dbConnection->real_escape_string($epGroupID), $this->dbConnection->real_escape_string($createdBy));
					}
				}
				
				if($insertAssociation != ""){
					$insertAssociation = substr($insertAssociation, 0, -1);
					
					$bulkQuery = sprintf("INSERT INTO endpointAssociations (`endpointId`, `macAddress`, `epGroupID`, `createdBy`) VALUES%s", $insertAssociation);
					
					$bulkQueryResult = $this->dbConnection->query($bulkQuery);
					
					if($bulkQueryResult){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}					
			}
		}
		
		function addEndpointAssociation($endpointId, $macAddress, $epGroupID, $createdBy){
			
			$query = "INSERT INTO endpointAssociations (`endpointId`, `macAddress`, `epGroupID`, `createdBy`) VALUES('$endpointId','$macAddress','$epGroupID','$createdBy')";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function addWirelessNetwork($ssidName, $ssidDescription, $createdBy){
			
			$query = sprintf("INSERT INTO wirelessNetworks (`ssidName`, `ssidDescription`, `createdBy`) VALUES('%s','%s','%s')", $this->dbConnection->real_escape_string($ssidName), $this->dbConnection->real_escape_string($ssidDescription), $this->dbConnection->real_escape_string($createdBy));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function addAuthorizationTemplate($authzPolicyName, $authzPolicyDescription, $ciscoAVPairPSK, $termLengthSeconds, $pskLength, $createdBy){
			
			$query = sprintf("INSERT INTO authorizationTemplates (`authzPolicyName`, `authzPolicyDescription`, `ciscoAVPairPSK`, `termLengthSeconds`, `pskLength`, `createdBy`) VALUES('%s','%s','%s',%d,%d,'%s')", $this->dbConnection->real_escape_string($authzPolicyName), $this->dbConnection->real_escape_string($authzPolicyDescription), $this->dbConnection->real_escape_string($ciscoAVPairPSK), $this->dbConnection->real_escape_string($termLengthSeconds), $this->dbConnection->real_escape_string($pskLength), $this->dbConnection->real_escape_string($createdBy));

			$queryResult = $this->dbConnection->query($query);

			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function addSponsorGroup($groupName, $groupDescription, $sponsorGroupAuthType, $members, $maxDevices, $groupType, $createdBy){
			
			$query = sprintf("INSERT INTO sponsorGroups (`sponsorGroupName`, `sponsorGroupDescription`, `sponsorGroupType`, `sponsorGroupAuthType`, `maxDevices`, `members`, `createdBy`) VALUES('%s','%s','%d','%d',%d,'%s','%s')", $this->dbConnection->real_escape_string($groupName), $this->dbConnection->real_escape_string($groupDescription), $this->dbConnection->real_escape_string($groupType), $this->dbConnection->real_escape_string($sponsorGroupAuthType), $this->dbConnection->real_escape_string($maxDevices), $this->dbConnection->real_escape_string(""), $this->dbConnection->real_escape_string($createdBy));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}

		function addInternalUser($userName, $fullName, $description, $email, $encryptedpassword, $createdBy){
			
			$query = sprintf("INSERT INTO internalUsers (`userName`, `fullName`, `description`, `email`, `password`, `dn`, `createdBy`) VALUES('%s','%s','%s','%s','%s','%s','%s')", $this->dbConnection->real_escape_string($userName), $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $this->dbConnection->real_escape_string($encryptedpassword), "CN=".$this->dbConnection->real_escape_string($userName).",CN=Users,DC=System,DC=Local", $this->dbConnection->real_escape_string($createdBy));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				$insertId = $this->dbConnection->insert_id;
				
				$userSidId = 1000 + $insertId;
				$userSid = "S-1-9-1556668800-1556668800-$userSidId";
				
				$sidQuery = "UPDATE `internalUsers` SET `sid`='$userSid' WHERE `id` = '$insertId'";
				
				$queryResult = $this->dbConnection->query($sidQuery);
								
				return $insertId;
			}else{
				return false;
			}
		}
		
		function addInternalGroup($groupName, $groupType, $groupDescription, $groupDn, $permission, $createdBy){
			
			if($groupType == 0){
				$groupDnUpdate = "CN=".$groupName.",CN=Users,DC=System,DC=Local";
			}else{
				$groupDnUpdate = $groupDn;
			}
			
			$query = sprintf("INSERT INTO internalGroups (`groupName`, `groupType`, `description`, `groupDn`, `permissions`, `createdBy`) VALUES('%s','%d','%s','%s',%d,'%s')", $this->dbConnection->real_escape_string($groupName), $this->dbConnection->real_escape_string($groupType), $this->dbConnection->real_escape_string($groupDescription), $this->dbConnection->real_escape_string($groupDnUpdate), $this->dbConnection->real_escape_string($permission), $this->dbConnection->real_escape_string($createdBy));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}
		
		function addSponsorPortal($portalName, $portalDescription, $portalType, $portalGuid, $sponsorHostname, $tcpPortId, $authDirectory, $createdBy){
			
			$portProtocol = $this->getTcpPortListById($tcpPortId);
			
			if($portProtocol){
				
				$query = sprintf("INSERT INTO sponsorPortals (`portalName`, `description`, `portalId`, `portalType`, `enabled`, `visible`, `portalHostname`, `portalTcpPort`, `portalSecure`, `authenticationDirectory`, `createdBy`) VALUES('%s','%s','%s','%d','1','1','%s','%d','%d','%d','%s')", $this->dbConnection->real_escape_string($portalName), $this->dbConnection->real_escape_string($portalDescription), $this->dbConnection->real_escape_string($portalGuid), $this->dbConnection->real_escape_string($portalType), $this->dbConnection->real_escape_string($sponsorHostname), $this->dbConnection->real_escape_string($portProtocol['portalPort']), $this->dbConnection->real_escape_string($portProtocol['portalSecure']), $this->dbConnection->real_escape_string($authDirectory), $this->dbConnection->real_escape_string($createdBy), $this->dbConnection->real_escape_string($createdBy));
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					return $this->dbConnection->insert_id;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

		function addSponsorInternalGroupMapping($internalGroupIds, $sponsorGroupId, $permissions, $createdBy){
			$querysuffix = "";
			
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "('$sponsorGroupId','$internalGroupIds[$idCount]','$permissions','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO sponsorGroupInternalMapping (`sponsorGroupId`, `internalGroupId`, `groupPermissions`, `createdBy`) VALUES %s", $querysuffix);

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function addSponsorGroupPortalMapping($internalGroupIds, $sponsorPortalId, $createdBy){
			$querysuffix = "";
			
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "('$internalGroupIds[$idCount]','$sponsorPortalId','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO sponsorGroupPortalMapping (`sponsorGroupId`, `sponsorPortalId`, `createdBy`) VALUES %s", $querysuffix);

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function addSponsorGroupSSIDMapping($wirelessIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
			
			if(is_array($wirelessIds)){
				for($idCount = 0; $idCount < count($wirelessIds); $idCount++){
					$querysuffix .= "('$sponsorGroupId','$wirelessIds[$idCount]','$createdBy'),";
				}
			}else{
				$querysuffix .= "('$sponsorGroupId','$wirelessIds','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO sponsorGroupSSIDMapping (`sponsorGroupId`, `wirelessSSIDId`, `createdBy`) VALUES %s", $querysuffix);
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function addSponsorGroupEPGMapping($endpointGroupIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
			
			if(is_array($endpointGroupIds)){
				for($idCount = 0; $idCount < count($endpointGroupIds); $idCount++){
					$querysuffix .= "('$sponsorGroupId','$endpointGroupIds[$idCount]','$createdBy'),";
				}
			}else{
				$querysuffix .= "('$sponsorGroupId','$endpointGroupIds','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO sponsorGroupEPGMapping (`sponsorGroupId`, `endpointGroupId`, `createdBy`) VALUES %s", $querysuffix);

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function addEndpointGroup($epGroupName, $epGroupDescription, $authzTemplate, $notificationPermission, $createdBy){
			
			$query = sprintf("INSERT INTO endpointGroups (`groupName`, `description`, `authzTemplateId`, `notificationPermission`, `createdBy`) VALUES('%s','%s',%d, %d,'%s')", $this->dbConnection->real_escape_string($epGroupName), $this->dbConnection->real_escape_string($epGroupDescription), $this->dbConnection->real_escape_string($authzTemplate), $this->dbConnection->real_escape_string($notificationPermission), $this->dbConnection->real_escape_string($createdBy));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function addUserCacheEntry($sid, $userPrincipalName, $samAccountName, $userDn, $systemSid){
			
			$query = sprintf("INSERT INTO `userSidCache` (`sid`, `userPrincipalName`, `samAccountName`, `userDn`,`createdBy`) VALUES('%s','%s','%s','%s','%s')", $this->dbConnection->real_escape_string($sid), $this->dbConnection->real_escape_string($userPrincipalName), $this->dbConnection->real_escape_string($samAccountName), $this->dbConnection->real_escape_string($userDn), $systemSid);
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function addLdapServer($adConnectionName, $adServer, $adDomain, $adUsername, $encryptedPassword, $adBaseDN, $adSecure, $createdBy){
			
			
			$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

			$ciphertext = sodium_crypto_secretbox($encryptedPassword, $nonce, base64_decode($this->encryptionKey));

			$encryptedPassword = base64_encode($nonce . $ciphertext);
						
			$query = sprintf("INSERT INTO `ldapServers` (`adConnectionName`, `adServer`, `adDomain`, `adUsername`, `adPassword`, `adBaseDN`, `adSecure`, `createdBy`) VALUES('%s','%s','%s','%s','%s','%s','%s','%s')", $this->dbConnection->real_escape_string($adConnectionName), $this->dbConnection->real_escape_string($adServer), $this->dbConnection->real_escape_string($adDomain), $this->dbConnection->real_escape_string($adUsername), $this->dbConnection->real_escape_string($encryptedPassword), $this->dbConnection->real_escape_string($adBaseDN), $this->dbConnection->real_escape_string($adSecure), $this->dbConnection->real_escape_string($createdBy));

			$queryResult = $this->dbConnection->query($query);

			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}
		
		function addHostname($hostname, $createdBy){
					
			$query = sprintf("INSERT INTO `portalHostnames` (`hostname`, `createdBy`) VALUES('%s','%s')", $this->dbConnection->real_escape_string($hostname), $this->dbConnection->real_escape_string($createdBy));

			$queryResult = $this->dbConnection->query($query);

			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}

		function addProtocolPort($protocol, $port, $createdBy){
					
			$query = sprintf("INSERT INTO `portalPorts` (`portalSecure`, `portalPort`, `createdBy`) VALUES('%s','%s','%s')", $this->dbConnection->real_escape_string($protocol), $this->dbConnection->real_escape_string($port), $this->dbConnection->real_escape_string($createdBy));

			$queryResult = $this->dbConnection->query($query);

			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}
		
		function deleteWirelessNetworkById($id){
			
			$query = sprintf("UPDATE wirelessNetworks SET `visible`='0' WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteInternalUserGroupMappingById($id){
			
			$query = sprintf("DELETE FROM `internalUserGroupMapping` WHERE `groupId` = %d", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteInternalUserGroupMappingByUserId($id){
			
			$query = sprintf("DELETE FROM `internalUserGroupMapping` WHERE `userId` = %d", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function deletePortalGroupMappingById($id){
			
			$query = sprintf("DELETE FROM `sponsorGroupInternalMapping` WHERE `internalGroupId` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function deleteInternalGroupById($id){
			
			$query = sprintf("DELETE FROM `internalGroups` WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function deleteInternalUserById($id){
			
			$query = sprintf("DELETE FROM `internalUsers` WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteLdapServerById($id){
			
			$query = sprintf("DELETE FROM `ldapServers` WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteAuthTemplateById($id){
			
			$query = sprintf("UPDATE authorizationTemplates SET `visible`='0' WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteEndpointById($id){
			
			$query = sprintf("DELETE FROM `endpoints` WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteEndpointGroupById($id){
			
			$query = sprintf("UPDATE endpointGroups SET `visible`='0' WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteEndpointAssociationbyId($id){
			
			$query = sprintf("DELETE FROM `endpointAssociations` WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteSponsorGroupById($id){
			
			$query = sprintf("UPDATE sponsorGroups SET `visible`='0' WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteSponsorPortalById($id){
			
			$query = sprintf("UPDATE sponsorPortals SET `visible`='0', `enabled`='0' WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			print $this->dbConnection->error;
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function deleteHostnameById($ids){
			
			if(is_array($ids)){
				$removeIds = "";
				
				foreach($ids as $idEntry){
					$removeIds .= "'$idEntry',";
				}
				
				$removeIds = substr($removeIds, 0, -1);

				$query = "DELETE FROM `portalHostnames` WHERE `id` IN ($removeIds)";
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}		

		function deleteProtocolPortById($ids){
			
			if(is_array($ids)){
				$removeIds = "";
				
				foreach($ids as $idEntry){
					$removeIds .= "'$idEntry',";
				}
				
				$removeIds = substr($removeIds, 0, -1);

				$query = "DELETE FROM `portalPorts` WHERE `id` IN ($removeIds)";
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function updateSponsorPortal($portalId, $portalName, $portalDescription, $portalType, $sponsorHostname, $tcpPortId, $authDirectory, $createdBy){
			
			$portProtocol = $this->getTcpPortListById($tcpPortId);
			
			if($portProtocol){
				
				$query = sprintf("UPDATE sponsorPortals SET `portalName`='%s', `description`='%s', `portalType`='%d', `portalHostname`='%s', `portalTcpPort`='%s', `portalSecure`='%d', `authenticationDirectory`='%d' WHERE id='%d'", $this->dbConnection->real_escape_string($portalName), $this->dbConnection->real_escape_string($portalDescription), $this->dbConnection->real_escape_string($portalType), $this->dbConnection->real_escape_string($sponsorHostname), $this->dbConnection->real_escape_string($portProtocol['portalPort']), $this->dbConnection->real_escape_string($portProtocol['portalSecure']), $this->dbConnection->real_escape_string($authDirectory), $this->dbConnection->real_escape_string($portalId));
				
				$queryResult = $this->dbConnection->query($query);
				
				if($queryResult){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}		
		
		function updateSponsorGroupPortalMapping($sponsorGroupIds, $sponsorPortalId, $createdBy){
			$querysuffix = "";
			
			$deleteQuery = "DELETE FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = '$sponsorPortalId'";
			
			$queryResult = $this->dbConnection->query($deleteQuery);
						
			for($idCount = 0; $idCount < count($sponsorGroupIds); $idCount++){
				$querysuffix .= "('$sponsorGroupIds[$idCount]','$sponsorPortalId','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO sponsorGroupPortalMapping (`sponsorGroupId`, `sponsorPortalId`, `createdBy`) VALUES %s", $querysuffix);

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateInternalGroup($groupId, $groupName, $groupType, $groupDescription, $groupDn, $permission, $createdBy){
			
			if($groupType == 0){
				$groupDnUpdate = "CN=".$groupName.",CN=Users,DC=System,DC=Local";
			}else{
				$groupDnUpdate = $groupDn;
			}
			
			$query = sprintf("UPDATE `internalGroups` SET `groupName`='%s', `groupType`='%d', `description`='%s', `groupDn`='%s', `permissions`='%d' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($groupName), $this->dbConnection->real_escape_string($groupType), $this->dbConnection->real_escape_string($groupDescription), $this->dbConnection->real_escape_string($groupDnUpdate), $this->dbConnection->real_escape_string($permission), $this->dbConnection->real_escape_string($groupId));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function updateInternalUserGroupMapping($internalGroupIds, $userId, $createdBy){
			$querysuffix = "";
			
			$deleteQuery = "DELETE FROM `internalUserGroupMapping` WHERE `userId` = '$userId'";
			
			$queryResult = $this->dbConnection->query($deleteQuery);
	
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "('$internalGroupIds[$idCount]','$userId','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO `internalUserGroupMapping` (`groupId`, `userId`, `createdBy`) VALUES %s", $querysuffix);

			$queryResult = $this->dbConnection->query($query);

			if($queryResult){
				return true;
			}else{
				
				return false;
			}
		}
		
		function updateWirelessNetwork($ssidId, $ssidName, $ssidDescription, $createdBy){
			
			$query = sprintf("UPDATE `wirelessNetworks` SET `ssidName` = '%s', `ssidDescription` = '%s' WHERE `id` = %d", $ssidName, $ssidDescription, $ssidId);

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateAuthorizationTemplate($authzId, $authzPolicyName, $authzPolicyDescription, $ciscoAVPairPSK, $termLengthSeconds, $pskLength, $createdBy){
			
			$query = sprintf("UPDATE `authorizationTemplates` SET `authzPolicyName`='%s', `authzPolicyDescription`='%s', `ciscoAVPairPSK`='%s', `termLengthSeconds`='%d', `pskLength`='%d' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($authzPolicyName), $this->dbConnection->real_escape_string($authzPolicyDescription), $this->dbConnection->real_escape_string($ciscoAVPairPSK), $this->dbConnection->real_escape_string($termLengthSeconds), $this->dbConnection->real_escape_string($pskLength), $this->dbConnection->real_escape_string($authzId));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateEndpointGroup($epGroupId, $epGroupName, $epGroupDescription, $authzTemplate, $notifyPermission, $createdBy){
			
			$query = sprintf("UPDATE `endpointGroups` SET `groupName`='%s', `description`='%s', `authzTemplateId`='%d', `notificationPermission`='%d' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($epGroupName), $this->dbConnection->real_escape_string($epGroupDescription), $this->dbConnection->real_escape_string($authzTemplate), $this->dbConnection->real_escape_string($notifyPermission), $this->dbConnection->real_escape_string($epGroupId));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateSponsorGroupEPGMapping($endpointGroupIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
			
			$deleteQuery = "DELETE FROM `sponsorGroupEPGMapping` WHERE `sponsorGroupId` = '$sponsorGroupId'";
			
			$queryResult = $this->dbConnection->query($deleteQuery);
			
			if(is_array($endpointGroupIds)){
				for($idCount = 0; $idCount < count($endpointGroupIds); $idCount++){
					$querysuffix .= "('$sponsorGroupId','$endpointGroupIds[$idCount]','$createdBy'),";
				}
			}else{
				$querysuffix .= "('$sponsorGroupId','$endpointGroupIds','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO `sponsorGroupEPGMapping` (`sponsorGroupId`, `endpointGroupId`, `createdBy`) VALUES %s", $querysuffix);

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateSponsorGroupSSIDMapping($wirelessIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
			
			$deleteQuery = "DELETE FROM `sponsorGroupSSIDMapping` WHERE `sponsorGroupId` = '$sponsorGroupId'";
			
			$queryResult = $this->dbConnection->query($deleteQuery);
			
			if(is_array($wirelessIds)){
				for($idCount = 0; $idCount < count($wirelessIds); $idCount++){
					$querysuffix .= "('$sponsorGroupId','$wirelessIds[$idCount]','$createdBy'),";
				}
			}else{
				$querysuffix .= "('$sponsorGroupId','$wirelessIds','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO sponsorGroupSSIDMapping (`sponsorGroupId`, `wirelessSSIDId`, `createdBy`) VALUES %s", $querysuffix);
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateSponsorInternalGroupMapping($internalGroupIds, $sponsorGroupId, $permissions, $createdBy){
			$querysuffix = "";
			
			$deleteQuery = "DELETE FROM `sponsorGroupInternalMapping` WHERE `sponsorGroupId` = '$sponsorGroupId'";
			
			$queryResult = $this->dbConnection->query($deleteQuery);
			
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "('$sponsorGroupId','$internalGroupIds[$idCount]','$permissions','$createdBy'),";
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$query = sprintf("INSERT INTO `sponsorGroupInternalMapping` (`sponsorGroupId`, `internalGroupId`, `groupPermissions`, `createdBy`) VALUES %s", $querysuffix);

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateSponsorGroup($groupId, $groupName, $groupDescription, $sponsorGroupAuthType, $members, $maxDevices, $groupType, $createdBy){
			
			$query = sprintf("UPDATE `sponsorGroups` SET `sponsorGroupName`='%s', `sponsorGroupDescription`='%s', `sponsorGroupType`='%d', `sponsorGroupAuthType`='%d', `maxDevices`='%d', `members`='%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($groupName), $this->dbConnection->real_escape_string($groupDescription), $this->dbConnection->real_escape_string($groupType), $this->dbConnection->real_escape_string($sponsorGroupAuthType), $this->dbConnection->real_escape_string($maxDevices), $this->dbConnection->real_escape_string(""), $this->dbConnection->real_escape_string($groupId));
			
			$queryResult = $this->dbConnection->query($query);
	
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateInternalUser($userId, $userName, $fullName, $description, $email, $createdBy){
			
			$query = sprintf("UPDATE `internalUsers` SET `userName`='%s', `fullName`='%s', `description`='%s', `email`='%s', `dn`='%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($userName), $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), "CN=".$this->dbConnection->real_escape_string($userName).",CN=Users,DC=System,DC=Local", $this->dbConnection->real_escape_string($userId));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function updateLdapServer($serverId, $adConnectionName, $adServer, $adDomain, $adUsername, $encryptedPassword, $adBaseDN, $adSecure, $createdBy){
			
			$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

			$ciphertext = sodium_crypto_secretbox($encryptedPassword, $nonce, base64_decode($this->encryptionKey));

			$encryptedPassword = base64_encode($nonce . $ciphertext);
						
			$query = sprintf("UPDATE `ldapServers` SET `adConnectionName`='%s', `adServer`='%s', `adDomain`='%s', `adUsername`='%s', `adPassword`='%s', `adBaseDN`='%s', `adSecure`='%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($adConnectionName), $this->dbConnection->real_escape_string($adServer), $this->dbConnection->real_escape_string($adDomain), $this->dbConnection->real_escape_string($adUsername), $this->dbConnection->real_escape_string($encryptedPassword), $this->dbConnection->real_escape_string($adBaseDN), $this->dbConnection->real_escape_string($adSecure), $this->dbConnection->real_escape_string($serverId));

			$queryResult = $this->dbConnection->query($query);

			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function updateEndpoint($endpointId, $fullName, $description, $email, $createdBy, $psk = null, $expirationDate = null){
			
			if($psk == null && $expirationDate == null){
				$query = sprintf("UPDATE `endpoints` SET `fullName` = '%s', `description` = '%s', `emailAddress` = '%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $this->dbConnection->real_escape_string($endpointId));
			}elseif($psk != null && $expirationDate == null){
				$query = sprintf("UPDATE `endpoints` SET `fullName` = '%s', `description` = '%s', `emailAddress` = '%s', `pskValue` = '%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $this->dbConnection->real_escape_string($psk), $this->dbConnection->real_escape_string($endpointId));
			}else{
				$query = sprintf("UPDATE `endpoints` SET `fullName` = '%s', `description` = '%s', `emailAddress` = '%s', `pskValue` = '%s', `expirationDate` = %d WHERE `id` = '%d'", $this->dbConnection->real_escape_string($fullName), $this->dbConnection->real_escape_string($description), $this->dbConnection->real_escape_string($email), $this->dbConnection->real_escape_string($psk), $this->dbConnection->real_escape_string($expirationDate), $this->dbConnection->real_escape_string($endpointId));
			}
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function updateEndpointPsk($endpointId, $psk){
			
			$query = sprintf("UPDATE `endpoints` SET `pskValue` = '%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($psk), $this->dbConnection->real_escape_string($endpointId));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}

		function extendEndpoint($endpointId, $termLengthSeconds, $createdBy){
			
			$query = sprintf("UPDATE `endpoints` SET `expirationDate` = %d WHERE `id` = '%d'", $this->dbConnection->real_escape_string($termLengthSeconds), $this->dbConnection->real_escape_string($endpointId));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
	
		function resetUserPassword($userId, $encryptedpassword, $createdBy){
			
			$query = sprintf("UPDATE `internalUsers` SET `password`='%s' WHERE `id` = '%d'", $this->dbConnection->real_escape_string($encryptedpassword), $this->dbConnection->real_escape_string($userId));

			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function suspendEndpointAssociationbyId($id){
			
			$query = sprintf("UPDATE `endpoints` SET `accountEnabled` = 0 WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function activateEndpointAssociationbyId($id){
			
			$query = sprintf("UPDATE `endpoints` SET `accountEnabled` = 1 WHERE `id` = %s", $this->dbConnection->real_escape_string($id));
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
		}
		
		function getDashboardStatsbyEPGroup(){
			$query = 'SELECT endpointGroups.groupName, COUNT(endpointAssociations.macAddress) as endpointCount FROM `endpointAssociations` INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId GROUP BY endpointGroups.id';
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getEndpointGroupCount(){
			$query = 'SELECT COUNT(groupName) as endpointGroupCount FROM `endpointGroups`';
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row['endpointGroupCount'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getTotalEndpointCount(){
			$query = 'SELECT COUNT(endpointAssociations.macAddress) as endpointCount FROM `endpointAssociations` INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId';
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row['endpointCount'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getTotalExpiredEndpointCount(){
			$query = "SELECT COUNT(endpointAssociations.macAddress) as endpointCount FROM `endpointAssociations` INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId WHERE endpoints.accountExpired = 1 OR endpoints.expirationDate < '".time()."'";
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row['endpointCount'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getTotalUnknownEndpointCount(){
			$query = 'SELECT COUNT(macAddress) as unknownEndpointCount FROM `unknownEndpoints`';
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row['unknownEndpointCount'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	
		function addLogEntry($message, $filename, $functionName = '', $className = '', $classMethodName = '', $lineNumber = 0, $data = ""){
			
			if($this->loggingLevel & 1){
				$this->addSQLLogEntry($message, $filename, $functionName , $className , $classMethodName , $lineNumber, $data);
			}
			
		}
		
		function addSQLLogEntry($message, $filename, $functionName = '', $className = '', $classMethodName = '', $lineNumber = 0, $data = ""){
			
			if($this->loggingLevel & 4){
				$payloadData = $data;
			}else{
				$payloadData = '';
			}
				
			$query = sprintf("INSERT INTO `logging` (`sessionID`, `fileName`, `functionName`, `className`,`classMethodName`, `lineNumber`, `message`, `logDataPayload`) VALUES('%s','%s','%s','%s','%s',%d,'%s','%s')", $_SESSION['sessionID'], $filename, $functionName, $className, $classMethodName, $lineNumber, $this->dbConnection->real_escape_string($message), $this->dbConnection->real_escape_string($payloadData));
			
			
			$queryResult = $this->dbConnection->query($query);
			
			if($queryResult){
				return true;
			}else{
				return false;
			}
			
		}
		
		function generateLogData(...$inputData){
			$inputIndex = 0;
			
			if($this->loggingLevel & 16){
				foreach($inputData AS $keyname => $data){
					$dataArray = array_keys($data);
					
					$logData['variables'][$inputIndex] = $dataArray[0];
					
					$logData[$inputIndex]['variableName'] = $dataArray[0];
					$logData[$inputIndex]['variableData'] = $data[$dataArray[0]];
					
					//Clear Known Sensitive Data from Log
					if($dataArray[0] == "sanitizedInput"){
						unset($logData[$inputIndex]['variableData']['password']);
						unset($logData[$inputIndex]['variableData']['confirmpassword']);
						unset($logData[$inputIndex]['variableData']['presharedKey']);
						unset($logData[$inputIndex]['variableData']['ersPassword']);
						unset($logData[$inputIndex]['variableData']['mntPassword']);
						unset($logData[$inputIndex]['variableData']['ciscoAVPairPSK']);
					}elseif($dataArray[0] == "ldapCreds"){
						unset($logData[$inputIndex]['variableData']['adPassword']);
					}
					
					$inputIndex++;
				}
			}
			
			if($this->loggingLevel & 32){
				$logData['variables'][$inputIndex] = "_GET";
					
				$logData[$inputIndex]['variableName'] = "_GET";
				$logData[$inputIndex]['variableData'] = $_GET;
				
				$inputIndex++;
			}
			
			if($this->loggingLevel & 64){
				$logData['variables'][$inputIndex] = "_POST";
					
				$logData[$inputIndex]['variableName'] = "_POST";
				$logData[$inputIndex]['variableData'] = $_POST;
				
				//Clear Known Sensitive Data from Log
				unset($logData[$inputIndex]['variableData']['inputPassword']);
				
				$inputIndex++;
			}
			
			if($this->loggingLevel & 128){
				$logData['variables'][$inputIndex] = "_SESSION";
					
				$logData[$inputIndex]['variableName'] = "_SESSION";
				$logData[$inputIndex]['variableData'] = $_SESSION;
				
				$inputIndex++;
			}
			
			if($this->loggingLevel & 256){
				$logData['variables'][$inputIndex] = "_SERVER";
					
				$logData[$inputIndex]['variableName'] = "_SERVER";
				$logData[$inputIndex]['variableData'] = $_SERVER;
				
				//Clear Known Sensitive Data from Log
				unset($logData[$inputIndex]['variableData']['ENC_KEY']);
				
				$inputIndex++;
			}
			
			if($inputIndex != 0){
				$logData['variables']['count'] = $inputIndex;
			
				return json_encode($logData);
			}else{
				return "";
			}
		}
	}
	
?>