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

	include_once(__DIR__."/iPSKManagerMigration.php");
	
	class iPSKManagerDatabase {
	
		public $requiredSchemaVersion = 7;
		public $autoMigrationBaselineVersion = 6;
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
		private $migrationPath;
		
		private $dbConnection;
	
		function __construct($hostname = null, $username = null, $password = null, $database = null) {		
			$this->dbHostname = $hostname;
			$this->dbUsername = $username;
			$this->dbPassword = $password;
			$this->dbDatabase = $database;
			$this->migrationPath = (realpath(__DIR__."/../db/migrations") !== false) ? realpath(__DIR__."/../db/migrations") : __DIR__."/../db/migrations";
			
			if($hostname != null && $username != null && $password != null && $database != null){
				$this->dbConnection = new mysqli($hostname, $username, $password, $database);
				
				if ($this->dbConnection->connect_error) {
					die('Connect Error (' . $this->dbConnection->connect_errno . ') '  . $this->dbConnection->connect_error);
				}
				
				$queryValues = [];
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'platform-config' AND `keyName` = 'system-configured' LIMIT 1";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
			
				if($queryResult){
					$row = $queryResult->fetch_assoc();
					
					$this->systemConfigured = $row["value"];
				}
				
				$queryValues = [];
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'db-schema' AND `keyName` = 'version' LIMIT 1";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
			
				if($queryResult){
					$row = $queryResult->fetch_assoc();
					
					$this->dbSchemaVersion = $row["value"];
				}
				
				$queryValues = [];
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'platform-config' AND `keyName` = 'logging-level' LIMIT 1";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
			
				if($queryResult){
					$row = $queryResult->fetch_assoc();
					
					$this->loggingLevel = $row["value"];
				}else{
					$this->loggingLevel = 0;
				}
				
				$queryValues = [];
				$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = 'advanced-settings' AND `keyName` = 'password-complexity' LIMIT 1";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
			
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

		function get_requiredSchemaVersion(){
			return $this->requiredSchemaVersion;
		}

		function set_dbSchemaVersion($version){
			$version = (int)$version;
			$this->dbSchemaVersion = $version;

			$updateStmt = $this->dbConnection->prepare("UPDATE `settings` SET `value` = ? WHERE `page` = 'global' AND `settingClass` = 'db-schema' AND `keyName` = 'version' LIMIT 1");
			
			if($updateStmt){
				$updateStmt->bind_param("i", $version);
				$updateStmt->execute();
				
				if($updateStmt->affected_rows == 0){
					$insertStmt = $this->dbConnection->prepare("INSERT INTO `settings` (`page`, `settingClass`, `keyName`, `value`) VALUES ('global', 'db-schema', 'version', ?)");
					
					if($insertStmt){
						$insertStmt->bind_param("i", $version);
						$insertStmt->execute();
						$insertStmt->close();
					}
				}
				
				$updateStmt->close();
			}
		}

		function check_dbSchemaUpdates(){
			if($this->requiredSchemaVersion > $this->dbSchemaVersion){
				return true;
			}else{
				return false;
			}
		}

		function get_migrationReplacements($extraReplacements = Array()){
			$replacements = Array(
				"{{DB_NAME}}" => $this->dbDatabase,
				"{{APP_DB_USERNAME}}" => $this->dbUsername
			);

			if(isset($_SERVER['IPSK_DB_USERNAME']) && $_SERVER['IPSK_DB_USERNAME'] != ""){
				$replacements["{{IPSK_DB_USERNAME}}"] = $_SERVER['IPSK_DB_USERNAME'];
			}

			if(isset($_SERVER['ISE_DB_USERNAME']) && $_SERVER['ISE_DB_USERNAME'] != ""){
				$replacements["{{ISE_DB_USERNAME}}"] = $_SERVER['ISE_DB_USERNAME'];
			}

			if(is_array($extraReplacements) && count($extraReplacements) > 0){
				$replacements = array_merge($replacements, $extraReplacements);
			}

			return $replacements;
		}

		function getMigrationManager($extraReplacements = Array()){
			return new iPSKManagerMigration($this->dbConnection, $this->dbDatabase, $this->migrationPath, $this->get_migrationReplacements($extraReplacements));
		}

		function getPendingSchemaMigrations($targetVersion = null){
			$migrationRunner = $this->getMigrationManager();
			$currentVersion = max((int)$this->dbSchemaVersion, (int)$this->autoMigrationBaselineVersion);
			$target = ($targetVersion != null) ? (int)$targetVersion : (int)$this->requiredSchemaVersion;

			return $migrationRunner->getPendingMigrations($currentVersion, $target);
		}

		function runSchemaMigrations($targetVersion = null){
			$response = Array(
				"success" => true,
				"applied" => Array(),
				"error" => "",
				"targetVersion" => ($targetVersion != null) ? (int)$targetVersion : (int)$this->requiredSchemaVersion
			);

			$migrationRunner = $this->getMigrationManager();
			$currentVersionRaw = (int)$this->dbSchemaVersion;
			$baselineVersion = (int)$this->autoMigrationBaselineVersion;

			if($currentVersionRaw < $baselineVersion){
				$response["success"] = false;
				$response["error"] = "Automatic migrations begin at schema version ".$baselineVersion.". Please apply earlier updates manually.";
				return $response;
			}

			$currentVersion = max($currentVersionRaw, $baselineVersion);
			$pendingMigrations = $migrationRunner->getPendingMigrations($currentVersion, $response["targetVersion"]);

			foreach($pendingMigrations as $migration){
				$execResult = $migrationRunner->executeMigration($migration);

				if(!$execResult["success"]){
					$response["success"] = false;
					$response["error"] = $execResult["error"];
					return $response;
				}

				$this->set_dbSchemaVersion($migration["version"]);
				$response["applied"][] = $migration["filename"];
			}

			return $response;
		}

		function check_newInstall(){

			$checkTableEmpty = function ($table) {
				$queryValues = [];
				$query = "SELECT COUNT(id) as count FROM `$table`";
				$queryResult = $this->preparedQuery($query, $queryValues);
		
				if ($queryResult) {
					$row = $queryResult->fetch_assoc();
					return (int)($row['count'] ?? 0) === 0;
				}
				return false;
			};
		
			$tables = ['endpoints', 'sponsorPortals'];
			foreach ($tables as $table) {
				if (!$checkTableEmpty($table)) {
					return false;
				}
			}
		
			return true;
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
			
			$queryValues = [$settingClass, $settingName, $settingIndex];
			$query = "SELECT `value` FROM `settings` WHERE `page` = 'global' AND `settingClass` = ? AND `keyName` = ? AND `optionIndex` = ?";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				return $queryResult->num_rows;
			}else{
				return false;
			}
		}

		function getGlobalClassSetting($className){
			$queryValues = [$className];
			$query = "SELECT * from settings WHERE settingClass=? AND page='global' AND encrypted='0'";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
			$queryValues = [$settingClass, $settingName, $settingIndex];
			$query = "SELECT `value`, `encrypted` FROM `settings` WHERE `page` = 'global' AND `settingClass` = ? AND `keyName` = ? AND `optionIndex` = ? LIMIT 1";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
				$queryUpdate = "UPDATE `settings` SET `value` = ?,`encrypted` = ? WHERE `page` = 'global' AND `settingClass` = ? AND `keyName` = ? AND `optionIndex` = ?";
			
				$queryResult = $this->preparedQuery($queryUpdate, [$value, $encryptedField, $settingClass, $settingName, $settingIndex]);

				if($queryResult){
					return true;
				}else{
					return false;
				}

			}else{	
				$queryInsert = "INSERT INTO `settings` (`value`, `page`, `settingClass`, `keyName`, `optionIndex`,`encrypted`) VALUES(?,'global',?,?,?,?)";
				
				$queryResult = $this->preparedQuery($queryInsert, [$value, $settingClass, $settingName, $settingIndex, $encryptedField]);
				
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
				$queryValues = [];
				$query = "SELECT `keyName`, `value`, `encrypted` FROM `settings` WHERE `page`='global' AND `settingClass`='smtp-settings'";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
			
			$queryValues = [];
			$query = "SELECT `groupDn` FROM `internalGroups` WHERE `permissions` = '1'";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
				$queryValues = [];
				$query = "SELECT `id`, `groupName`, `description`, `groupType`, `groupDn`, `permissions`, `visible`, `createdBy`, `createdDate` FROM `internalGroups`";
			}else{
				$queryValues = [$groupType];
				$query = "SELECT `id`, `groupName`, `description`, `groupType`, `groupDn`, `permissions`, `visible`, `createdBy`, `createdDate` FROM `internalGroups` WHERE groupType = ?";
			}
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
			$queryValues = [$groupId];
			$query = "SELECT `id`, `groupName`, `description`, `groupType`, `groupDn`, `permissions`, `visible`, `createdBy`, `createdDate` FROM `internalGroups` WHERE id = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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

			$queryValues = [$userId];
			$query = "SELECT `internalUserGroupMapping`.`userId`,`internalUserGroupMapping`.`groupId`, `internalGroups`.`id`, `internalGroups`.`groupName`, `internalGroups`.`groupDn` FROM `internalUserGroupMapping` INNER JOIN `internalGroups` ON internalUserGroupMapping.groupId =  `internalGroups`.`id` WHERE `internalUserGroupMapping`.`userId` = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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

			$queryValues = [$username];
			$query = "SELECT `id`,`userName`,`fullName`,`description`,`createdBy`,`createdDate` FROM `internalUsers` WHERE userName = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
			$queryValues = [];
			$query = "SELECT `id`, `userName`, `description`, `fullName` FROM `internalUsers`";

			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
			$queryValues = [$userId];
			$query = "SELECT `id`, `userName`, `description`, `fullName`, `email`, `dn`, `sid`, `enabled`, `createdBy`, `createdDate` FROM `internalUsers` WHERE id = ?";

			$queryResult = $this->preparedQuery($query, $queryValues);
			
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

		function authenticateInternalUser($username,$password,$saml = false){
            ipskResetIdentity();
			
			$passwordVerify = false;
			
			$authQueryValues = [$username];
			$authQuery = "SELECT password FROM `internalUsers` WHERE userName = ? LIMIT 1";
			
			$authQueryResult = $this->preparedQuery($authQuery, $authQueryValues);
			
			if($authQueryResult){
				if($authQueryResult->num_rows > 0){
					$user = $authQueryResult->fetch_assoc();
					
					if($saml == false) {
						$passwordVerify = password_verify($password,$user['password']);
					}

					if($passwordVerify == true || $saml == true){
						
						$userQueryValues = [$username];
						$userQuery = "SELECT `id`,`userName`,`fullName`,`description`, `email`, `dn`,`sid`,`createdBy`,`createdDate` FROM `internalUsers` WHERE userName = ? LIMIT 1";
			
						$userQueryResult = $this->preparedQuery($userQuery, $userQueryValues);
						
						$user = $userQueryResult->fetch_assoc();
					

						$membershipQueryValues = [$user['id']];
						$membershipQuery = "SELECT `internalUserGroupMapping`.`userId`,`internalUserGroupMapping`.`groupId`, `internalGroups`.`id`, `internalGroups`.`groupName`, `internalGroups`.`groupDn` FROM `internalUserGroupMapping` INNER JOIN `internalGroups` ON internalUserGroupMapping.groupId =  `internalGroups`.`id` WHERE `internalUserGroupMapping`.`userId` = ?";

						$groupMembership = $this->preparedQuery($membershipQuery, $membershipQueryValues);
						
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
						ipskRotateSession();
                            $_SESSION['authenticationGranted'] = true;
						$_SESSION['authenticationTimestamp'] = time();
						$_SESSION['logonTime'] = time();
						$_SESSION['loggedIn'] = true;
						if(isset($_SESSION['logoutTimer'])) {
							unset($_SESSION['logoutTimer']);
						}
						
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
			$queryValues = [];
			$query = "SELECT * FROM portalHostnames WHERE enabled = '1' AND visible = '1'";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$id];
			$query = "SELECT `id`, `portalPort`, `portalSecure` FROM `portalPorts` WHERE enabled = '1' AND visible = '1' AND `id` = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT `id`, `portalPort`, `portalSecure` FROM `portalPorts` WHERE enabled = '1' AND visible = '1'";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT id, adConnectionName FROM `ldapServers`";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT COUNT(id) as ldapCount FROM `ldapServers`";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT id, adConnectionName FROM `ldapServers`";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT id, adConnectionName, adServer, adDomain, adUsername, adPassword, adBaseDN, adSecure, createdBy, createdDate FROM `ldapServers`";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$directoryId];
			$query = "SELECT id, adConnectionName, adServer, adDomain, adUsername, adPassword, adBaseDN, adSecure, directoryType, createdBy, createdDate FROM `ldapServers` WHERE id = ?";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
			$queryValues = [$ldapID];
			$query = "SELECT adConnectionName, adServer, adDomain, adUsername, adPassword, adBaseDN, adSecure, directoryType FROM `ldapServers` WHERE id = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
						$result['directoryType'] = $ldapServer['directoryType'];
						
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
			$queryValues = [$sid];
			$query = "SELECT `id`, `userPrincipalName` FROM `userSidCache` WHERE `sid`=? LIMIT 1";
		
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
				$queryValues = [$instanceId];
				$query = "SELECT * from settings WHERE settingClass='ise-mnt-credentials' AND page='global' AND optionIndex=?";
			
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
				$queryValues = [$instanceId];
				$query = "SELECT * from settings WHERE settingClass='ise-ers-credentials' AND page='global' AND optionIndex=?";
			
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
				
				$queryValues = [$endpointGroupId, $sid];
				$query = "SELECT endpoints.createdBy, COUNT(endpointAssociations.epGroupId) as deviceCount FROM endpointAssociations INNER JOIN endpoints ON endpointAssociations.endpointId = endpoints.id WHERE endpointAssociations.epGroupId = ? AND endpoints.createdBy = ? GROUP BY endpoints.createdBy";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						$row = $queryResult->fetch_assoc();
						
						if($row['createdBy'] == $sid){
							return (int)$row['deviceCount'];
						}else{
							return false;
						}
					}else{
						return 0;
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
				
				$queryValues = [$endpointGroupId, $sid];
				$query = "SELECT endpoints.createdBy, COUNT(endpointAssociations.epGroupId) as deviceCount, endpoints.pskValue FROM endpointAssociations INNER JOIN endpoints ON endpointAssociations.endpointId = endpoints.id WHERE endpointAssociations.epGroupId = ? AND endpoints.createdBy = ? GROUP BY endpoints.createdBy, endpoints.pskValue";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
			$queryValues = [];
			$query = "SELECT * from wirelessNetworks WHERE visible = 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$endpointGroupId];
			$query = "SELECT endpointGroups.id, endpointGroups.groupName, endpointGroups.notificationPermission FROM endpointGroups WHERE endpointGroups.id = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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

		function purgeLogs($interval){
			$queryValues = [(int)$interval];
			$query = "DELETE FROM logging WHERE dateCreated < NOW() - INTERVAL ? DAY";

			$queryResult = $this->preparedQuery($query, $queryValues);

		}
		
		function getPxGridDirectEndpoints(){
			$queryValues = [];
			$query = "SELECT endpoints.id, endpoints.macAddress, 
						CASE endpoints.accountEnabled
							WHEN 0 THEN 'False'
							WHEN 1 THEN 'True'
						ELSE endpoints.accountEnabled
						END AS accountEnabled, endpoints.accountExpired, endpoints.fullName, endpoints.description, endpoints.createdDate, FROM_UNIXTIME(endpoints.expirationDate) AS expirationDate, endpoints.pskValue, REPLACE(endpoints.pskValue, 'psk=', '') AS pskValuePlain, endpointGroups.groupName, endpointGroups.description AS groupDescription, endpoints.lastUpdated
						FROM endpoints JOIN endpointAssociations ON endpoints.id = endpointAssociations.endpointId 
						JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id;";

			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
		
		function getEndpointGroups(){
			$queryValues = [];
			$query = "SELECT `id`,`groupName`,`description`, `enabled`, `authzTemplateId`, `visible`, `notificationPermission`, `createdBy`, `createdDate` FROM `endpointGroups` WHERE visible=1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT endpointGroups.id, endpointGroups.groupName, endpointGroups.authzTemplateId, endpointGroups.description, endpointGroups.enabled, endpointGroups.visible, endpointGroups.notificationPermission, endpointGroups.parentSite, authorizationTemplates.authzPolicyName, authorizationTemplates.authzPolicyDescription, authorizationTemplates.ciscoAVPairPSKMode, authorizationTemplates.ciscoAVPairPSK, authorizationTemplates.termLengthSeconds, endpointGroups.createdBy, endpointGroups.createdDate FROM endpointGroups INNER JOIN authorizationTemplates ON authorizationTemplates.id = endpointGroups.authzTemplateId";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$endpointGroupId];
			$query = "SELECT endpointGroups.id, endpointGroups.groupName, endpointGroups.authzTemplateId, endpointGroups.description, endpointGroups.enabled, endpointGroups.visible, endpointGroups.notificationPermission, endpointGroups.parentSite, authorizationTemplates.authzPolicyName, authorizationTemplates.authzPolicyDescription, authorizationTemplates.ciscoAVPairPSKMode, authorizationTemplates.ciscoAVPairPSK, authorizationTemplates.termLengthSeconds, endpointGroups.createdBy, endpointGroups.createdDate FROM endpointGroups INNER JOIN authorizationTemplates ON authorizationTemplates.id = endpointGroups.authzTemplateId WHERE endpointGroups.id = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$portalId];
			$query = "SELECT endpointGroups.id,endpointGroups.groupName FROM `endpointGroups` INNER JOIN sponsorGroupEPGMapping ON endpointGroups.id = sponsorGroupEPGMapping.endpointGroupId INNER JOIN sponsorGroupPortalMapping ON sponsorGroupEPGMapping.sponsorGroupId = sponsorGroupPortalMapping.sponsorGroupId WHERE endpointGroups.enabled = 1 AND endpointGroups.visible = 1 AND sponsorGroupPortalMapping.sponsorPortalId = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT id,groupName FROM endpointGroups WHERE visible=1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
                $searchDnsValues = [];
								
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "?,";
                    $searchDnsValues[] = $authorizationGroups[$count]['groupDn'];
				}
				
				$searchDns = $searchDnsValues ? substr($searchDns, 0, -1) : "NULL";
				
				$queryValues = [$portalId];
				$query = "SELECT internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions, sponsorGroupEPGMapping.endpointGroupId, endpointGroups.groupName, sponsorGroups.maxDevices, sponsorGroupPortalMapping.sponsorGroupId, authorizationTemplates.termLengthSeconds, authorizationTemplates.ciscoAVPairPSK FROM sponsorGroupPortalMapping INNER JOIN sponsorGroups ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN internalGroups ON sponsorGroupInternalMapping.internalGroupId = internalGroups.id INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.sponsorGroupId = sponsorGroups.id INNER JOIN endpointGroups ON sponsorGroupEPGMapping.endpointGroupId = endpointGroups.id INNER JOIN authorizationTemplates ON endpointGroups.authzTemplateId = authorizationTemplates.id WHERE sponsorGroupPortalMapping.sponsorPortalId = ? AND internalGroups.groupDn IN ($searchDns) AND sponsorGroupInternalMapping.sponsorGroupId IN (SELECT id FROM sponsorGroups WHERE visible=1)";
				array_splice($queryValues, 1, 0, $searchDnsValues);
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
						
						return ipskMergeGroupGrants($queryData);
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
			$queryValues = [$associationId];
			$query = "SELECT authorizationTemplates.termLengthSeconds, endpointAssociations.id, endpointAssociations.epGroupId, endpointAssociations.macAddress, endpoints.fullName, endpoints.id as endpointId, endpoints.description, endpoints.emailAddress, endpoints.pskValue, endpoints.createdBy FROM endpointAssociations INNER JOIN endpointGroups  ON endpointAssociations.epGroupId = endpointGroups.id INNER JOIN authorizationTemplates  ON authorizationTemplates.id = endpointGroups.authzTemplateId INNER JOIN endpoints  ON endpointAssociations.endpointId = endpoints.id WHERE endpointAssociations.id = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$id];
			$query = "SELECT endpoints.id, endpoints.macAddress, endpoints.createdBy, endpointGroups.authzTemplateId FROM `endpoints` INNER JOIN endpointAssociations ON endpoints.id = endpointAssociations.endpointId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id WHERE endpointGroups.authzTemplateId = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$endpointList['count'] = 0;
					$itemCount = 0;
					
					while($row = $queryResult->fetch_assoc()){
						$endpointList[$itemCount]['id'] = $row['id'];
						$endpointList[$itemCount]['macAddress'] = $row['macAddress'];
						$endpointList[$itemCount]['createdBy'] = $row['createdBy'];
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
			$queryValues = [];
			$query = "SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, endpoints.macAddress, endpoints.createdBy, endpoints.description, endpointGroups.groupName as epGroupName, endpoints.accountEnabled, endpoints.expirationDate, endpointAssociations.createdDate, endpoints.fullName, endpoints.emailAddress FROM endpointAssociations INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId ORDER BY endpoints.macAddress ASC";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
						$rawAssociationList[$listCount]['fullName'] = $row['fullName'];
						$rawAssociationList[$listCount]['email'] = $row['emailAddress'];
						$rawAssociationList[$listCount]['description'] = $row['description'];
						
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
		
		function getEndPointAuthorizationPolicy($associationId){
			$queryValues = [$associationId];
			$query = "SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, authorizationTemplates.pskLength FROM endpointAssociations INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id INNER JOIN authorizationTemplates ON authorizationTemplates.id = endpointGroups.authzTemplateId INNER JOIN endpoints  ON endpointAssociations.endpointId = endpoints.id WHERE endpointAssociations.id = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
		
		function getEndPointAssociationList($authorizationGroups, $sponsorPortalId = 0, $viewAll = false, $viewallDn = ""){
			$searchDns = "";
                $searchDnsValues = [];
			
			if($sponsorPortalId == 0){
				return false;
			}
			
			if(is_array($authorizationGroups)){
				$searchDns = "";
                $searchDnsValues = [];
				
				
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "?,";
                    $searchDnsValues[] = $authorizationGroups[$count]['groupDn'];
				}
				
				$searchDns = $searchDnsValues ? substr($searchDns, 0, -1) : "NULL";
				
				if($viewAll == true){
					$queryValues = [$sponsorPortalId, $viewallDn];
					$query = "SELECT id, endpointId, epGroupId, sponsorGroupId, macAddress, createdBy, expirationDate, accountEnabled, accountExpired, pskValue, fullName, description, emailAddress, createdDate, groupDn, groupPermissions, groupName FROM (SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, sponsorGroupEPGMapping.sponsorGroupId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions, endpointGroups.groupName FROM endpoints INNER JOIN endpointAssociations ON endpoints.id = endpointAssociations.endpointId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id WHERE internalGroups.groupDn IN ($searchDns) AND sponsorGroupInternalMapping.sponsorGroupId IN (SELECT id FROM sponsorGroups WHERE visible=1) AND sponsorGroupEPGMapping.sponsorGroupId IN (SELECT sponsorGroupId FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = ?) UNION SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, '0' as sponsorGroupId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, ? as groupDn, '4' as groupPermissions, endpointGroups.groupName FROM endpointAssociations INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id GROUP BY endpointAssociations.id, endpointAssociations.endpointId) AS completeEndpointList ORDER BY macAddress ASC";
					array_splice($queryValues, 0, 0, $searchDnsValues);
				}else{
					$queryValues = [$sponsorPortalId];
					$query = "SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.epGroupId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions, endpointGroups.groupName FROM endpoints INNER JOIN endpointAssociations ON endpoints.id = endpointAssociations.endpointId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId INNER JOIN endpointGroups ON endpointAssociations.epGroupId = endpointGroups.id WHERE internalGroups.groupDn IN ($searchDns) AND sponsorGroupInternalMapping.sponsorGroupId IN (SELECT id FROM sponsorGroups WHERE visible=1) AND sponsorGroupEPGMapping.sponsorGroupId IN (SELECT sponsorGroupId FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = ?) ORDER BY endpointAssociations.macAddress ASC";
					array_splice($queryValues, 0, 0, $searchDnsValues);
				}
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
                $searchDnsValues = [];
				
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "?,";
                    $searchDnsValues[] = $authorizationGroups[$count]['groupDn'];
				}
				
				$searchDns = $searchDnsValues ? substr($searchDns, 0, -1) : "NULL";
				
				$queryValues = [$endpointAssociationId, $sponsorPortalId];
				$query = "SELECT id, endpointId, macAddress, createdBy, endpointGroupId, groupName, accountEnabled, expirationDate, accountExpired, pskValue, fullName, description, emailAddress, createdDate, groupDn, groupPermissions FROM (SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.macAddress, endpoints.createdBy, endpoints.lastAccessed, endpointGroups.id as endpointGroupId, endpointGroups.groupName, endpoints.expirationDate, endpoints.accountEnabled, endpoints.accountExpired, endpoints.pskValue, endpoints.fullName, endpoints.description, endpoints.emailAddress, endpointAssociations.createdDate, sponsorGroupEPGMapping.sponsorGroupId, sponsorGroupInternalMapping.internalGroupId, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions FROM endpointAssociations INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId INNER JOIN sponsorGroupEPGMapping ON sponsorGroupEPGMapping.endpointGroupId = endpointAssociations.epGroupId INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN sponsorGroupPortalMapping ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId WHERE endpointAssociations.id = ? AND groupDn IN ($searchDns) AND sponsorGroupInternalMapping.sponsorGroupId IN (SELECT id FROM sponsorGroups WHERE visible=1) AND sponsorGroupEPGMapping.sponsorGroupId IN (SELECT sponsorGroupId FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = ?)) AS completePermissions GROUP BY id, groupDn, groupPermissions";
				array_splice($queryValues, 1, 0, $searchDnsValues);
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
				if($queryResult){
					if($queryResult->num_rows > 0){
						while($row = $queryResult->fetch_assoc()){
                            // Apply visibility scope before merging action bits from multiple grants.
                            if (!(($row['groupPermissions'] & 6) ||
                                (($row['groupPermissions'] & 1) && $row['createdBy'] === ($_SESSION['logonSID'] ?? null)))) continue;
							
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
						
						foreach($rawEndpointList ?? [] as $endPoint){
							$mergedEndpointList[$listCount]['id'] = $endPoint['id'];
							$mergedEndpointList[$listCount]['endpointId'] = $endPoint['endpointId'];
							$mergedEndpointList[$listCount]['macAddress'] = $endPoint['macAddress'];
							$mergedEndpointList[$listCount]['viewPermissions'] = $endPoint['groupPermissions'] & 15;
							$mergedEndpointList[$listCount]['advancedPermissions'] = $endPoint['groupPermissions'] & 2032;
							$mergedEndpointList[$listCount]['groupPermissions'] = $endPoint['groupPermissions'] & 4095;

							$listCount++;
						}
						
						if ($listCount === 0) return false;
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
			$queryValues = [$endpointGroupId];
			$query = "SELECT endpointAssociations.id, endpointAssociations.endpointId, endpointAssociations.macAddress, endpointAssociations.createdBy, endpoints.createdDate as epCreatedDate, endpointGroups.id as epGroupId, endpointGroups.groupName as epGroupName, endpoints.macAddress, endpoints.expirationDate, endpoints.accountExpired, endpoints.accountEnabled, endpoints.fullName, endpoints.description, endpoints.pskValue, endpoints.lastAccessed, endpoints.emailAddress, endpointAssociations.createdDate, endpoints.vlan, endpoints.dacl FROM endpointAssociations INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId WHERE endpointAssociations.id = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
		
		function getEndPointAssociationByEndpoint($endpointId){
			$queryValues = [$endpointId];
			$query = "SELECT id FROM `endpointAssociations` WHERE endpointId = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					$row = $queryResult->fetch_assoc();
					return $row['id'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function getAuthorizationTemplates(){
			$queryValues = [];
			$query = "SELECT * FROM authorizationTemplates WHERE visible = 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT id,authzPolicyName FROM authorizationTemplates";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$templateId];
			$query = "SELECT * FROM authorizationTemplates WHERE id = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
		
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
			$queryValues = [$id];
			$query = "SELECT authorizationTemplates.ciscoAVPairPSK, authorizationTemplates.termLengthSeconds, authorizationTemplates.pskLength, authorizationTemplates.vlan, authorizationTemplates.dacl, endpointGroups.groupName FROM `authorizationTemplates` INNER JOIN endpointGroups ON authorizationTemplates.id = endpointGroups.authzTemplateId WHERE endpointGroups.id = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
		
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
			$queryValues = [];
			$query = "SELECT * FROM sponsorPortals WHERE visible = '1'";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
		
        private $enrollmentLock = null;

        function beginPortalEnrollment($groupId, $macs, $permission = 512) {
            if ($this->enrollmentLock !== null || !is_array($macs) || !$macs) return false;
            $lock = 'ipsk-quota-'.substr(hash('sha256', $this->dbDatabase.'|'.($_SESSION['logonSID'] ?? '').'|'.$groupId),0,53);
            try {
                $result = $this->preparedQuery('SELECT GET_LOCK(?, 10) AS acquired', [$lock]);
                if (!$result || (int)$result->fetch_assoc()['acquired'] !== 1) return false;
                $this->enrollmentLock = $lock;
                if (!$this->dbConnection->begin_transaction()) { $this->abortPortalEnrollment(); return false; }
                // Refresh grants and usage only after acquiring the cross-session lock.
                $grant = ipskCreationGrant($this, $groupId, $permission);
                if (!$grant) { $this->abortPortalEnrollment(); return false; }
                $unique = array_values(array_unique(array_map(function ($mac) { return strtoupper(str_replace('-', ':', $mac)); }, $macs)));
                $result = $this->preparedQuery('SELECT macAddress FROM endpoints WHERE macAddress IN ('.implode(',',array_fill(0,count($unique),'?')).')', $unique);
                if (!$result) { $this->abortPortalEnrollment(); return false; }
                $incoming = count($unique) - $result->num_rows;
                if (!ipskQuotaAvailable($this->getUserEndpointCount($groupId, $_SESSION['logonSID']), $grant['maxDevices'], $incoming)) {
                    $this->abortPortalEnrollment(); return false;
                }
                return true;
            } catch (Throwable $error) {
                $this->abortPortalEnrollment();
                return false;
            }
        }

        function finishPortalEnrollment() {
            if ($this->enrollmentLock === null) return false;
            try {
                if (!$this->dbConnection->commit()) { $this->abortPortalEnrollment(); return false; }
                $lock = $this->enrollmentLock;
                $this->enrollmentLock = null;
                $this->preparedQuery('SELECT RELEASE_LOCK(?)', [$lock]);
                return true;
            } catch (Throwable $error) { $this->abortPortalEnrollment(); return false; }
        }

        function abortPortalEnrollment() {
            if ($this->enrollmentLock === null) return;
            $lock = $this->enrollmentLock; $this->enrollmentLock = null;
            try { $this->dbConnection->rollback(); } catch (Throwable $error) {}
            try { $this->preparedQuery('SELECT RELEASE_LOCK(?)', [$lock]); } catch (Throwable $error) {}
        }

        function __destruct() { $this->abortPortalEnrollment(); }

        private function preparedQuery($sql, $values = []) {
            $statement = $this->dbConnection->prepare($sql);
            if (!$statement) return false;
            try {
                if ($values && !$statement->bind_param(str_repeat('s', count($values)), ...$values)) return false;
                if (!$statement->execute()) return false;
                return $statement->field_count ? $statement->get_result() : true;
            } finally {
                $statement->close();
            }
        }

		function getPortalbyHostname($portalFQDN){
			$queryValues = [];
			$query = "SELECT * FROM sponsorPortals WHERE portalHostname=? AND enabled = '1' AND visible = '1' LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, [$portalFQDN]);
			
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
            if (!is_string($portalGuid) || !preg_match('/\A[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}\z/iD', $portalGuid)) return false;
			$queryValues = [];
			$query = "SELECT sponsorPortals.id, sponsorPortals.portalName, sponsorPortals.description, sponsorPortals.portalId, sponsorPortals.portalType, sponsorPortals.enabled, sponsorPortals.visible, sponsorPortals.portalHostname, sponsorPortals.portalTcpPort, sponsorPortals.enforceTcpPort, sponsorPortals.enforceSecure, sponsorPortals.enforceHostname, sponsorPortals.portalSecure, sponsorPortals.portalTemplate, sponsorPortals.authenticationDirectory, sponsorPortals.createdBy, sponsorPortals.createdDate, sponsorPortalTypes.portalTypeName, sponsorPortalTypes.portalTypeDescription, sponsorPortalTypes.maxSponsorGroups, sponsorPortalTypes.maxEndpointsOverride, sponsorPortalTypes.portalClass, sponsorPortalTypes.portalModule FROM sponsorPortals INNER JOIN sponsorPortalTypes ON sponsorPortalTypes.id = sponsorPortals.portalType WHERE sponsorPortals.portalId=? AND sponsorPortals.enabled=1 AND sponsorPortals.visible=1";
			
			$queryResult = $this->preparedQuery($query, [$portalGuid]);
			
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
			$queryValues = [$id];
			$query = "SELECT sponsorPortals.id, sponsorPortals.portalName, sponsorPortals.description, sponsorPortals.portalId, sponsorPortals.portalType, sponsorPortals.enabled, sponsorPortals.visible, sponsorPortals.portalHostname, sponsorPortals.portalTcpPort, sponsorPortals.enforceTcpPort, sponsorPortals.enforceSecure, sponsorPortals.enforceHostname, sponsorPortals.portalSecure, sponsorPortals.portalTemplate, sponsorPortals.authenticationDirectory, sponsorPortals.createdBy, sponsorPortals.createdDate, sponsorPortalTypes.portalTypeName, sponsorPortalTypes.portalTypeDescription, sponsorPortalTypes.maxSponsorGroups, sponsorPortalTypes.maxEndpointsOverride, sponsorPortalTypes.portalClass, sponsorPortalTypes.portalModule FROM sponsorPortals INNER JOIN sponsorPortalTypes ON sponsorPortalTypes.id = sponsorPortals.portalType WHERE sponsorPortals.id=?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
                $searchDnsValues = [];
								
				for($count = 0; $count < $authorizationGroups['count']; $count++){
					$searchDns .= "?,";
                    $searchDnsValues[] = $authorizationGroups[$count]['groupDn'];
				}
				
				$searchDns = $searchDnsValues ? substr($searchDns, 0, -1) : "NULL";
				
				$queryValues = [$portalId];
				$query = "SELECT sponsorGroupPortalMapping.sponsorGroupId, sponsorGroupSSIDMapping.wirelessSSIDId, wirelessNetworks.ssidName, internalGroups.groupDn, sponsorGroupInternalMapping.groupPermissions FROM sponsorGroupPortalMapping INNER JOIN sponsorGroups ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroups.id  INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN internalGroups ON sponsorGroupInternalMapping.internalGroupId = internalGroups.id INNER JOIN sponsorGroupSSIDMapping ON sponsorGroupSSIDMapping.sponsorGroupId = sponsorGroups.id  INNER JOIN wirelessNetworks ON sponsorGroupSSIDMapping.wirelessSSIDId = wirelessNetworks.id WHERE sponsorGroupPortalMapping.sponsorPortalId = ? AND internalGroups.groupDn IN ($searchDns) AND sponsorGroupInternalMapping.sponsorGroupId IN (SELECT id FROM sponsorGroups WHERE visible=1)";
				array_splice($queryValues, 1, 0, $searchDnsValues);
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
			
			$queryValues = [$portalId];
			$query = "SELECT internalGroups.groupDn, internalGroups.permissions, sponsorGroupInternalMapping.groupPermissions  FROM `sponsorGroupPortalMapping` INNER JOIN sponsorGroups ON sponsorGroupPortalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN sponsorGroupInternalMapping ON sponsorGroupInternalMapping.sponsorGroupId = sponsorGroups.id INNER JOIN internalGroups ON sponsorGroupInternalMapping.internalGroupId = internalGroups.id  WHERE sponsorGroups.visible=1 AND sponsorGroupPortalMapping.sponsorPortalId = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
			$queryValues = [$id];
			$query = "SELECT sponsorPortals.portalId, sponsorPortals.portalHostname, sponsorPortals.portalTcpPort, sponsorPortals.portalSecure FROM sponsorPortals WHERE sponsorPortals.id=? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$portalId];
			$query = "SELECT sponsorGroupSSIDMapping.id, wirelessNetworks.id as wirelessNetworkId, wirelessNetworks.ssidName FROM `sponsorGroupSSIDMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupSSIDMapping.sponsorGroupId INNER JOIN wirelessNetworks ON wirelessNetworks.id = sponsorGroupSSIDMapping.wirelessSSIDId WHERE sponsorGroupSSIDMapping.sponsorGroupId = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
				$queryValues = [$portalId];
				$query = "SELECT sponsorGroupInternalMapping.id, sponsorGroupInternalMapping.groupPermissions, sponsorGroups.id as sponsorGroupId, sponsorGroups.sponsorGroupName, internalGroups.id as internalGroupId, internalGroups.groupName as internalGroupName, internalGroups.groupDn as internalGroupDn FROM `sponsorGroupInternalMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupInternalMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId WHERE sponsorGroupInternalMapping.sponsorGroupId = ?";
			}else{
				$queryValues = [$portalId];
				$query = "SELECT sponsorGroupInternalMapping.id, sponsorGroupInternalMapping.groupPermissions, sponsorGroups.id as sponsorGroupId, sponsorGroups.sponsorGroupName, internalGroups.id as internalGroupId, internalGroups.groupName as internalGroupName FROM `sponsorGroupInternalMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupInternalMapping.sponsorGroupId INNER JOIN internalGroups ON internalGroups.id = sponsorGroupInternalMapping.internalGroupId WHERE sponsorGroupInternalMapping.sponsorGroupId = ?";
			}
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT `id`, `portalTypeName`, `portalTypeDescription`, `maxSponsorGroups`, `maxEndpointsOverride`, `maxEndpointsAllowed`, `portalClass`, `portalModule` FROM `sponsorPortalTypes`";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = "SELECT * FROM sponsorGroups WHERE visible = 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$id];
			$query = "SELECT * FROM sponsorGroups WHERE id=?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$portalId];
			$query = "SELECT sponsorGroupPortalMapping.id, sponsorGroups.sponsorGroupName, sponsorGroupPortalMapping.sponsorGroupId, sponsorGroupPortalMapping.sponsorPortalId FROM `sponsorGroupPortalMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupPortalMapping.sponsorGroupId WHERE sponsorGroupPortalMapping.sponsorPortalId = ?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$id];
			$query = "SELECT endpointGroups.id, endpointGroups.groupName FROM `sponsorGroupEPGMapping` INNER JOIN sponsorGroups ON sponsorGroups.id = sponsorGroupEPGMapping.sponsorGroupId INNER JOIN endpointGroups ON endpointGroups.id = sponsorGroupEPGMapping.endpointGroupId WHERE sponsorGroupEPGMapping.sponsorGroupId=?";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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

		function getLogging($minute = 60){
			$queryValues = [];
			$query = "SELECT `id`, `dateCreated`, `sessionID`, `fileName`, `functionName`, `className`, `classMethodName`, `lineNumber`, `message` FROM `logging` WHERE `dateCreated` BETWEEN DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL ? MINUTE) AND CURRENT_TIMESTAMP() ORDER BY `dateCreated` DESC";
			$queryValues = [(int)$minute];
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [$id];
			$query = "SELECT * FROM `logging` WHERE `id`=? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
		
		function getSponsorLoginsLastSevenDays() {
			$queryValues = [];
			$query = "SELECT COUNT(*) AS count, DATE(dateCreated) as date FROM `logging` WHERE `message` LIKE 'REQUEST:SUCCESS;ACTION:SPONSORAUTHN%' AND dateCreated >= date_sub(curdate(), interval 7 day) GROUP BY date;";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				} else{
					return false;
				}
			}else{
				return false;
			}

		}

		function getCaptiveLoginsLastSevenDays() {
			$queryValues = [];
			$query = "SELECT COUNT(*) AS count, DATE(dateCreated) as date FROM `logging` WHERE `message` LIKE 'REQUEST:SUCCESS;ACTION:CAPTIVEAUTHN%' AND dateCreated >= date_sub(curdate(), interval 7 day) GROUP BY date;";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				} else{
					return false;
				}
			}else{
				return false;
			}

		}

		function getAdminLoginsLastSevenDays() {
			$queryValues = [];
			$query = "SELECT COUNT(*) AS count, DATE(dateCreated) as date FROM `logging` WHERE `message` LIKE 'REQUEST:SUCCESS;ACTION:ADMINAUTHN%' AND dateCreated >= date_sub(curdate(), interval 7 day) GROUP BY date;";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				} else{
					return false;
				}
			}else{
				return false;
			}

		}

		function getAddedEndpointsLastSevenDays() {
			$queryValues = [];
			$query = "SELECT COUNT(*) AS count, DATE(createdDate) as date FROM `endpoints` WHERE createdDate >= date_sub(curdate(), interval 7 day) GROUP BY date;";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				if($queryResult->num_rows > 0){
					return $queryResult;
				} else{
					return false;
				}
			}else{
				return false;
			}

		}

		

		function getWirelessNetworkById($wirelessId){
			$queryValues = [$wirelessId];
			$query = "SELECT * FROM wirelessNetworks WHERE id = ? LIMIT 1";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
		
		function addEndpoint($macAddress, $fullName, $description, $email, $psk, $vlan, $dacl, $expirationDate, $createdBy){
			
            $endpointQueryValues = [];
            $endpointQuery = "SELECT id FROM `endpoints` WHERE `macAddress` = ?";
			
			$endpointQueryResult = $this->preparedQuery($endpointQuery, [$macAddress]);
			
			if($endpointQueryResult->num_rows < 1){
                $queryValues = [];
                $query = "INSERT INTO `endpoints` (`macAddress`, `password`, `fullName`, `description`, `emailAddress`, `pskValue`, `vlan`, `dacl`, `expirationDate`, `createdBy`) VALUES (?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?)";
			
				$queryResult = $this->preparedQuery($query, [$macAddress, $macAddress, $fullName, $description, $email, $psk, $vlan, $dacl, $expirationDate, $createdBy]);
				
				if($queryResult){
					return $this->dbConnection->insert_id;
				}else{
					return false;
				}
			}else{
				$endpoint = $endpointQueryResult->fetch_assoc();
				//Duplicate MAC detected; do not mutate the existing endpoint record.
				return $endpoint['id'];
			}
			}
		
		function addBulkEndpoints($macAddress, $fullName, $description, $email, $psk, $vlan, $dacl, $expirationDate, $createdBy){
			$multiInput = false;
			$multipsk = false;
			
			if(is_array($psk)){
				$multipsk = true;
			}
			
			if(is_array($fullName) && is_array($description) && is_array($email)){
				$multiInput = true;
			}
			
			if(is_array($macAddress)){
				$searchMacAddress = "";
				
				foreach($macAddress as $entry){
					$searchMacAddress .= "?,";
				}
					
				$searchMacAddress = substr($searchMacAddress, 0, -1);
				
				$endpointQueryValues = [];
				$endpointQuery = sprintf("SELECT id, macAddress FROM `endpoints` WHERE `macAddress` IN (%s)", $searchMacAddress);
				
				$endpointQueryResult = $this->preparedQuery($endpointQuery, array_values($macAddress));
								
				if($endpointQueryResult->num_rows < 1){
					$insertMacAddress = "";
                    $insertValues = [];
					$macAddressAdd['count'] = 0;
					$macAddressAdd['skipped'] = 0;
					$macAddressAdd['processed'] = 0;
					$count = 0;

					if(count($macAddress) > 0){
						if($multiInput == true){
							foreach($macAddress as $entry => $key){
								if($multipsk){
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName[$entry], $description[$entry], $email[$entry], $psk[$entry], $vlan, $dacl, $expirationDate, $createdBy);
								}else{
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName[$entry], $description[$entry], $email[$entry], $psk, $vlan, $dacl, $expirationDate, $createdBy);
								}
							}
						}else{
							foreach($macAddress as $entry => $key){
								if($multipsk){
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName, $description, $email, $psk[$entry], $vlan, $dacl, $expirationDate, $createdBy);
								}else{
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName, $description, $email, $psk, $vlan, $dacl, $expirationDate, $createdBy);
								}
							}
						}
						
						$insertMacAddress = substr($insertMacAddress, 0, -1);
						
						$bulkQueryValues = [];
						$bulkQuery = sprintf("INSERT INTO `endpoints` (`macAddress`, `password`, `fullName`, `description`, `emailAddress`, `pskValue`, `vlan`, `dacl`, `expirationDate`, `createdBy`) VALUES%s", $insertMacAddress);
						
						$bulkQueryResult = $this->preparedQuery($bulkQuery, $insertValues);
						
						if($bulkQueryResult){
							
							$affectRows = $this->dbConnection->affected_rows;
							
							foreach($macAddress as $entry => $key){
								$macAddressAdd[$count]['macAddress'] = $key;
								$macAddressAdd[$count]['exists'] = false;
								$inserted = $this->preparedQuery('SELECT id FROM endpoints WHERE macAddress=?', [$key]);
                                $insertedRow = $inserted ? $inserted->fetch_assoc() : false;
                                if (!$insertedRow) return false;
                                $macAddressAdd[$count]['insertId'] = (int)$insertedRow['id'];
								
								if($multipsk){
									$macAddressAdd[$count]['psk'] = $psk[$entry];
								}else{
									$macAddressAdd[$count]['psk'] = $psk;
								}
								
								$count++;
								
								
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
                    $insertValues = [];
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
								if($multipsk){
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName[$entry], $description[$entry], $email[$entry], $psk[$entry], $vlan, $dacl, $expirationDate, $createdBy);
								}else{
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName[$entry], $description[$entry], $email[$entry], $psk, $vlan, $dacl, $expirationDate, $createdBy);
								}
							}
						}else{
							foreach($macAddress as $entry => $key){
								if($multipsk){
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName, $description, $email, $psk[$entry], $vlan, $dacl, $expirationDate, $createdBy);
								}else{
									$insertMacAddress .= "(?,LCASE(REPLACE(REPLACE(?,':',''),'-','')),?,?,?,?,?,?,?,?),";
									array_push($insertValues, $key, $key, $fullName, $description, $email, $psk, $vlan, $dacl, $expirationDate, $createdBy);
								}
							}
						}
						
						$insertMacAddress = substr($insertMacAddress, 0, -1);
						
						$bulkQueryValues = [];
						$bulkQuery = sprintf("INSERT INTO `endpoints` (`macAddress`, `password`, `fullName`, `description`, `emailAddress`, `pskValue`, `vlan`, `dacl`, `expirationDate`, `createdBy`) VALUES%s", $insertMacAddress);
						
						$bulkQueryResult = $this->preparedQuery($bulkQuery, $insertValues);
						
						if($bulkQueryResult){
							
							$affectRows = $this->dbConnection->affected_rows;
							
							foreach($macAddress as $entry => $key){
								$macAddressAdd[$count]['macAddress'] = $key;
								$macAddressAdd[$count]['exists'] = false;
								$inserted = $this->preparedQuery('SELECT id FROM endpoints WHERE macAddress=?', [$key]);
                                $insertedRow = $inserted ? $inserted->fetch_assoc() : false;
                                if (!$insertedRow) return false;
                                $macAddressAdd[$count]['insertId'] = (int)$insertedRow['id'];
								
								if($multipsk){
									$macAddressAdd[$count]['psk'] = $psk[$entry];
								}else{
									$macAddressAdd[$count]['psk'] = $psk;
								}
								
								$count++;
								
								
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
                $associationValues = [];
				
				for($rowCount = 0; $rowCount < $macAddress['count']; $rowCount++){
					if($macAddress[$rowCount]['exists'] != true){
						$insertAssociation .= "(?,?,?,?),";
						array_push($associationValues, $macAddress[$rowCount]['insertId'], $macAddress[$rowCount]['macAddress'], $epGroupID, $createdBy);
					}
				}
				
				if($insertAssociation != ""){
					$insertAssociation = substr($insertAssociation, 0, -1);
					
					$bulkQueryValues = [];
					$bulkQuery = sprintf("INSERT INTO endpointAssociations (`endpointId`, `macAddress`, `epGroupID`, `createdBy`) VALUES%s", $insertAssociation);
					
					$bulkQueryResult = $this->preparedQuery($bulkQuery, $associationValues);
					
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
			
			$queryValues = [];
			$query = "INSERT INTO endpointAssociations (`endpointId`, `macAddress`, `epGroupID`, `createdBy`) VALUES(?,?,?,?)";
			
			try {
				$queryResult = $this->preparedQuery($query, [$endpointId, $macAddress, $epGroupID, $createdBy]);
				return $queryResult !== false;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function addWirelessNetwork($ssidName, $ssidDescription, $createdBy){
			
			$queryValues = [$ssidName, $ssidDescription, $createdBy];
			$query = "INSERT INTO wirelessNetworks (`ssidName`, `ssidDescription`, `createdBy`) VALUES(?,?,?)";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function addAuthorizationTemplate($authzPolicyName, $authzPolicyDescription, $ciscoAVPairPSK, $termLengthSeconds, $pskLength, $vlan, $dacl, $createdBy){
			
			$queryValues = [$authzPolicyName, $authzPolicyDescription, $ciscoAVPairPSK, (int)($termLengthSeconds), (int)($pskLength), $vlan, $dacl, $createdBy];
			$query = "INSERT INTO authorizationTemplates (`authzPolicyName`, `authzPolicyDescription`, `ciscoAVPairPSK`, `termLengthSeconds`, `pskLength`, `vlan`, `dacl`, `createdBy`) VALUES(?,?,?,?,?,?,?,?)";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function addSponsorGroup($groupName, $groupDescription, $sponsorGroupAuthType, $members, $maxDevices, $groupType, $createdBy){
			
			$queryValues = [$groupName, $groupDescription, (int)($groupType), (int)($sponsorGroupAuthType), (int)($maxDevices), "", $createdBy];
			$query = "INSERT INTO sponsorGroups (`sponsorGroupName`, `sponsorGroupDescription`, `sponsorGroupType`, `sponsorGroupAuthType`, `maxDevices`, `members`, `createdBy`) VALUES(?,?,?,?,?,?,?)";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return $this->dbConnection->insert_id;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function addInternalUser($userName, $fullName, $description, $email, $encryptedpassword, $createdBy){
			
			$queryValues = [$userName, $fullName, $description, $email, $encryptedpassword, "CN=".$userName.",CN=Users,DC=System,DC=Local", $createdBy];
			$query = "INSERT INTO internalUsers (`userName`, `fullName`, `description`, `email`, `password`, `dn`, `createdBy`) VALUES(?,?,?,?,?,?,?)";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
			
				$insertId = $this->dbConnection->insert_id;
				
				$userSidId = 1000 + $insertId;
				$userSid = "S-1-9-1556668800-1556668800-$userSidId";
				
				$sidQueryValues = [$userSid, $insertId];
				$sidQuery = "UPDATE `internalUsers` SET `sid`=? WHERE `id` = ?";
				
				$queryResult = $this->preparedQuery($sidQuery, $sidQueryValues);
								
				return $insertId;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function addInternalGroup($groupName, $groupType, $groupDescription, $groupDn, $permission, $createdBy){
			
			if($groupType == 0){
				$groupDnUpdate = "CN=".$groupName.",CN=Users,DC=System,DC=Local";
			}else{
				$groupDnUpdate = $groupDn;
			}
			
			$queryValues = [$groupName, (int)($groupType), $groupDescription, $groupDnUpdate, (int)($permission), $createdBy];
			$query = "INSERT INTO internalGroups (`groupName`, `groupType`, `description`, `groupDn`, `permissions`, `createdBy`) VALUES(?,?,?,?,?,?)";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}
		
		function addSponsorPortal($portalName, $portalDescription, $portalType, $portalGuid, $sponsorHostname, $tcpPortId, $authDirectory, $createdBy){
			
			$portProtocol = $this->getTcpPortListById($tcpPortId);
			
			if($portProtocol){
				
				$queryValues = [$portalName, $portalDescription, $portalGuid, (int)($portalType), $sponsorHostname, (int)($portProtocol['portalPort']), (int)($portProtocol['portalSecure']), (int)($authDirectory), $createdBy];
				$query = "INSERT INTO sponsorPortals (`portalName`, `description`, `portalId`, `portalType`, `enabled`, `visible`, `portalHostname`, `portalTcpPort`, `portalSecure`, `authenticationDirectory`, `createdBy`) VALUES(?,?,?,?,'1','1',?,?,?,?,?)";
				
				$queryResult = $this->preparedQuery($query, $queryValues);
				
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
            $querysuffixValues = [];
			
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "(?,?,?,?),";
				array_push($querysuffixValues, $sponsorGroupId, $internalGroupIds[$idCount], $permissions, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO sponsorGroupInternalMapping (`sponsorGroupId`, `internalGroupId`, `groupPermissions`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function addSponsorGroupPortalMapping($internalGroupIds, $sponsorPortalId, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "(?,?,?),";
				array_push($querysuffixValues, $internalGroupIds[$idCount], $sponsorPortalId, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO sponsorGroupPortalMapping (`sponsorGroupId`, `sponsorPortalId`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function addSponsorGroupSSIDMapping($wirelessIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			if(is_array($wirelessIds)){
				for($idCount = 0; $idCount < count($wirelessIds); $idCount++){
					$querysuffix .= "(?,?,?),";
					array_push($querysuffixValues, $sponsorGroupId, $wirelessIds[$idCount], $createdBy);
				}
			}else{
				$querysuffix .= "(?,?,?),";
				array_push($querysuffixValues, $sponsorGroupId, $wirelessIds, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO sponsorGroupSSIDMapping (`sponsorGroupId`, `wirelessSSIDId`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function addSponsorGroupEPGMapping($endpointGroupIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			if(is_array($endpointGroupIds)){
				for($idCount = 0; $idCount < count($endpointGroupIds); $idCount++){
					$querysuffix .= "(?,?,?),";
					array_push($querysuffixValues, $sponsorGroupId, $endpointGroupIds[$idCount], $createdBy);
				}
			}else{
				$querysuffix .= "(?,?,?),";
				array_push($querysuffixValues, $sponsorGroupId, $endpointGroupIds, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO sponsorGroupEPGMapping (`sponsorGroupId`, `endpointGroupId`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function addEndpointGroup($epGroupName, $epGroupDescription, $authzTemplate, $notificationPermission, $createdBy){
			
			$queryValues = [$epGroupName, $epGroupDescription, (int)($authzTemplate), (int)($notificationPermission), $createdBy];
			$query = "INSERT INTO endpointGroups (`groupName`, `description`, `authzTemplateId`, `notificationPermission`, `createdBy`) VALUES(?,?,?, ?,?)";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function addUserCacheEntry($sid, $userPrincipalName, $samAccountName, $userDn, $systemSid){
			
			$queryValues = [$sid, $userPrincipalName, $samAccountName, $userDn, $systemSid];
			$query = "INSERT INTO `userSidCache` (`sid`, `userPrincipalName`, `samAccountName`, `userDn`,`createdBy`) VALUES(?,?,?,?,?)";
				
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function addLdapServer($adConnectionName, $adServer, $adDomain, $adUsername, $encryptedPassword, $adBaseDN, $adSecure, $directoryType, $createdBy){
			
			
			$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

			$ciphertext = sodium_crypto_secretbox($encryptedPassword, $nonce, base64_decode($this->encryptionKey));

			$encryptedPassword = base64_encode($nonce . $ciphertext);
						
			$queryValues = [$adConnectionName, $adServer, $adDomain, $adUsername, $encryptedPassword, $adBaseDN, $adSecure, $directoryType, $createdBy];
			$query = "INSERT INTO `ldapServers` (`adConnectionName`, `adServer`, `adDomain`, `adUsername`, `adPassword`, `adBaseDN`, `adSecure`, `directoryType`, `createdBy`) VALUES(?,?,?,?,?,?,?,?,?)";

			$queryResult = $this->preparedQuery($query, $queryValues);

			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}
		
		function addHostname($hostname, $createdBy){
					
			$queryValues = [$hostname, $createdBy];
			$query = "INSERT INTO `portalHostnames` (`hostname`, `createdBy`) VALUES(?,?)";

			$queryResult = $this->preparedQuery($query, $queryValues);

			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}

		function addProtocolPort($protocol, $port, $createdBy){
					
			$queryValues = [$protocol, $port, $createdBy];
			$query = "INSERT INTO `portalPorts` (`portalSecure`, `portalPort`, `createdBy`) VALUES(?,?,?)";

			$queryResult = $this->preparedQuery($query, $queryValues);

			if($queryResult){
				return $this->dbConnection->insert_id;
			}else{
				return false;
			}
		}
		
		function deleteWirelessNetworkById($id){
			
			$queryValues = [$id];
			$query = "UPDATE wirelessNetworks SET `visible`='0' WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteInternalUserGroupMappingById($id){
			
			$queryValues = [(int)($id)];
			$query = "DELETE FROM `internalUserGroupMapping` WHERE `groupId` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteInternalUserGroupMappingByUserId($id){
			
			$queryValues = [(int)($id)];
			$query = "DELETE FROM `internalUserGroupMapping` WHERE `userId` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function deletePortalGroupMappingById($id){
			
			$queryValues = [$id];
			$query = "DELETE FROM `sponsorGroupInternalMapping` WHERE `internalGroupId` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function deleteInternalGroupById($id){
			
			$queryValues = [$id];
			$query = "DELETE FROM `internalGroups` WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function deleteInternalUserById($id){
			
			$queryValues = [$id];
			$query = "DELETE FROM `internalUsers` WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteLdapServerById($id){
			
			$queryValues = [$id];
			$query = "DELETE FROM `ldapServers` WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteAuthTemplateById($id){
			
			$queryValues = [$id];
			$query = "UPDATE authorizationTemplates SET `visible`='0' WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteEndpointById($id){
			
			$queryValues = [$id];
			$query = "DELETE FROM `endpoints` WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteEndpointGroupById($id){
			
			$queryValues = [$id];
			$query = "UPDATE endpointGroups SET `visible`='0' WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteEndpointAssociationbyId($id){
			
			$queryValues = [$id];
			$query = "DELETE FROM `endpointAssociations` WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteSponsorGroupById($id){
			
			$queryValues = [$id];
			$query = "UPDATE sponsorGroups SET `visible`='0' WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteSponsorPortalById($id){
			
			$queryValues = [$id];
			$query = "UPDATE sponsorPortals SET `visible`='0', `enabled`='0' WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function deleteHostnameById($ids){
			
			if(is_array($ids)){
				$removeIds = "";
                $removeIdValues = [];
				
				foreach($ids as $idEntry){
					$removeIds .= "?,";
                    $removeIdValues[] = $idEntry;
				}
				
				$removeIds = $removeIdValues ? substr($removeIds, 0, -1) : "NULL";

				$queryValues = [];
				$query = "DELETE FROM `portalHostnames` WHERE `id` IN ($removeIds)";
				$queryValues = $removeIdValues;
				
				try {
					$queryResult = $this->preparedQuery($query, $queryValues);
					return true;
				}
				catch (Exception $e) {
					//error_log("Caught Exception: $e");
					return false;
				}
			}else{
				return false;
			}
		}		

		function deleteProtocolPortById($ids){
			
			if(is_array($ids)){
				$removeIds = "";
                $removeIdValues = [];
				
				foreach($ids as $idEntry){
					$removeIds .= "?,";
                    $removeIdValues[] = $idEntry;
				}
				
				$removeIds = $removeIdValues ? substr($removeIds, 0, -1) : "NULL";

				$queryValues = [];
				$query = "DELETE FROM `portalPorts` WHERE `id` IN ($removeIds)";
				$queryValues = $removeIdValues;
				
				try {
					$queryResult = $this->preparedQuery($query, $queryValues);
					return true;
				}
				catch (Exception $e) {
					//error_log("Caught Exception: $e");
					return false;
				}
			}else{
				return false;
			}
		}
		
		function updateSponsorPortal($portalId, $portalName, $portalDescription, $portalType, $sponsorHostname, $tcpPortId, $authDirectory, $createdBy){
			
			$portProtocol = $this->getTcpPortListById($tcpPortId);
			
			if($portProtocol){
				
				$queryValues = [$portalName, $portalDescription, (int)($portalType), $sponsorHostname, $portProtocol['portalPort'], (int)($portProtocol['portalSecure']), (int)($authDirectory), (int)($portalId)];
				$query = "UPDATE sponsorPortals SET `portalName`=?, `description`=?, `portalType`=?, `portalHostname`=?, `portalTcpPort`=?, `portalSecure`=?, `authenticationDirectory`=? WHERE id=?";
				
				try {
					$queryResult = $this->preparedQuery($query, $queryValues);
					return true;
				}
				catch (Exception $e) {
					//error_log("Caught Exception: $e");
					return false;
				}
			}else{
				return false;
			}
		}		
		
		function updateSponsorGroupPortalMapping($sponsorGroupIds, $sponsorPortalId, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			$deleteQueryValues = [$sponsorPortalId];
			$deleteQuery = "DELETE FROM `sponsorGroupPortalMapping` WHERE `sponsorPortalId` = ?";
			
			$queryResult = $this->preparedQuery($deleteQuery, $deleteQueryValues);
						
			for($idCount = 0; $idCount < count($sponsorGroupIds); $idCount++){
				$querysuffix .= "(?,?,?),";
				array_push($querysuffixValues, $sponsorGroupIds[$idCount], $sponsorPortalId, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO sponsorGroupPortalMapping (`sponsorGroupId`, `sponsorPortalId`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateInternalGroup($groupId, $groupName, $groupType, $groupDescription, $groupDn, $permission, $createdBy){
			
			if($groupType == 0){
				$groupDnUpdate = "CN=".$groupName.",CN=Users,DC=System,DC=Local";
			}else{
				$groupDnUpdate = $groupDn;
			}
			
			$queryValues = [$groupName, (int)($groupType), $groupDescription, $groupDnUpdate, (int)($permission), (int)($groupId)];
			$query = "UPDATE `internalGroups` SET `groupName`=?, `groupType`=?, `description`=?, `groupDn`=?, `permissions`=? WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function updateInternalUserGroupMapping($internalGroupIds, $userId, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			$deleteQueryValues = [$userId];
			$deleteQuery = "DELETE FROM `internalUserGroupMapping` WHERE `userId` = ?";
			
			$queryResult = $this->preparedQuery($deleteQuery, $deleteQueryValues);
	
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "(?,?,?),";
				array_push($querysuffixValues, $internalGroupIds[$idCount], $userId, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO `internalUserGroupMapping` (`groupId`, `userId`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateWirelessNetwork($ssidId, $ssidName, $ssidDescription, $createdBy){
			
			$queryValues = [$ssidName, $ssidDescription, (int)($ssidId)];
			$query = "UPDATE `wirelessNetworks` SET `ssidName` = ?, `ssidDescription` = ? WHERE `id` = ?";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateAuthorizationTemplate($authzId, $authzPolicyName, $authzPolicyDescription, $ciscoAVPairPSK, $termLengthSeconds, $pskLength, $vlan, $dacl, $createdBy){
			
			$queryValues = [$authzPolicyName, $authzPolicyDescription, $ciscoAVPairPSK, (int)($termLengthSeconds), (int)($pskLength), $vlan, $dacl, (int)($authzId)];
			$query = "UPDATE `authorizationTemplates` SET `authzPolicyName`=?, `authzPolicyDescription`=?, `ciscoAVPairPSK`=?, `termLengthSeconds`=?, `pskLength`=?, `vlan`=?, `dacl`=? WHERE `id` = ?";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateEndpointGroup($epGroupId, $epGroupName, $epGroupDescription, $authzTemplate, $notifyPermission, $createdBy){
			
			$queryValues = [$epGroupName, $epGroupDescription, (int)($authzTemplate), (int)($notifyPermission), (int)($epGroupId)];
			$query = "UPDATE `endpointGroups` SET `groupName`=?, `description`=?, `authzTemplateId`=?, `notificationPermission`=? WHERE `id` = ?";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateSponsorGroupEPGMapping($endpointGroupIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			$deleteQueryValues = [$sponsorGroupId];
			$deleteQuery = "DELETE FROM `sponsorGroupEPGMapping` WHERE `sponsorGroupId` = ?";
			
			$queryResult = $this->preparedQuery($deleteQuery, $deleteQueryValues);
			
			if(is_array($endpointGroupIds)){
				for($idCount = 0; $idCount < count($endpointGroupIds); $idCount++){
					$querysuffix .= "(?,?,?),";
					array_push($querysuffixValues, $sponsorGroupId, $endpointGroupIds[$idCount], $createdBy);
				}
			}else{
				$querysuffix .= "(?,?,?),";
				array_push($querysuffixValues, $sponsorGroupId, $endpointGroupIds, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO `sponsorGroupEPGMapping` (`sponsorGroupId`, `endpointGroupId`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateSponsorGroupSSIDMapping($wirelessIds, $sponsorGroupId, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			$deleteQueryValues = [$sponsorGroupId];
			$deleteQuery = "DELETE FROM `sponsorGroupSSIDMapping` WHERE `sponsorGroupId` = ?";
			
			$queryResult = $this->preparedQuery($deleteQuery, $deleteQueryValues);
			
			if(is_array($wirelessIds)){
				for($idCount = 0; $idCount < count($wirelessIds); $idCount++){
					$querysuffix .= "(?,?,?),";
					array_push($querysuffixValues, $sponsorGroupId, $wirelessIds[$idCount], $createdBy);
				}
			}else{
				$querysuffix .= "(?,?,?),";
				array_push($querysuffixValues, $sponsorGroupId, $wirelessIds, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO sponsorGroupSSIDMapping (`sponsorGroupId`, `wirelessSSIDId`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateSponsorInternalGroupMapping($internalGroupIds, $sponsorGroupId, $permissions, $createdBy){
			$querysuffix = "";
            $querysuffixValues = [];
			
			$deleteQueryValues = [$sponsorGroupId];
			$deleteQuery = "DELETE FROM `sponsorGroupInternalMapping` WHERE `sponsorGroupId` = ?";
			
			$queryResult = $this->preparedQuery($deleteQuery, $deleteQueryValues);
			
			for($idCount = 0; $idCount < count($internalGroupIds); $idCount++){
				$querysuffix .= "(?,?,?,?),";
				array_push($querysuffixValues, $sponsorGroupId, $internalGroupIds[$idCount], $permissions, $createdBy);
			}
			
			$querysuffix = substr($querysuffix,0, strlen($querysuffix) - 1);
			
			$queryValues = [];
			$query = sprintf("INSERT INTO `sponsorGroupInternalMapping` (`sponsorGroupId`, `internalGroupId`, `groupPermissions`, `createdBy`) VALUES %s", $querysuffix);
			$queryValues = $querysuffixValues;

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateSponsorGroup($groupId, $groupName, $groupDescription, $sponsorGroupAuthType, $members, $maxDevices, $groupType, $createdBy){
			
			$queryValues = [$groupName, $groupDescription, (int)($groupType), (int)($sponsorGroupAuthType), (int)($maxDevices), "", (int)($groupId)];
			$query = "UPDATE `sponsorGroups` SET `sponsorGroupName`=?, `sponsorGroupDescription`=?, `sponsorGroupType`=?, `sponsorGroupAuthType`=?, `maxDevices`=?, `members`=? WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateInternalUser($userId, $userName, $fullName, $description, $email, $createdBy){
			
			$queryValues = [$userName, $fullName, $description, $email, "CN=".$userName.",CN=Users,DC=System,DC=Local", (int)($userId)];
			$query = "UPDATE `internalUsers` SET `userName`=?, `fullName`=?, `description`=?, `email`=?, `dn`=? WHERE `id` = ?";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateLdapServer($serverId, $adConnectionName, $adServer, $adDomain, $adUsername, $encryptedPassword, $adBaseDN, $adSecure, $directoryType, $createdBy){
			
			if($encryptedPassword != null){
				$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

				$ciphertext = sodium_crypto_secretbox($encryptedPassword, $nonce, base64_decode($this->encryptionKey));

				$encryptedPassword = base64_encode($nonce . $ciphertext);
							
				$queryValues = [$adConnectionName, $adServer, $adDomain, $adUsername, $encryptedPassword, $adBaseDN, $adSecure, $directoryType, (int)($serverId)];
				$query = "UPDATE `ldapServers` SET `adConnectionName`=?, `adServer`=?, `adDomain`=?, `adUsername`=?, `adPassword`=?, `adBaseDN`=?, `adSecure`=?, `directoryType`=? WHERE `id` = ?";
			}else{
				$queryValues = [$adConnectionName, $adServer, $adDomain, $adUsername, $adBaseDN, $adSecure, $directoryType, (int)($serverId)];
				$query = "UPDATE `ldapServers` SET `adConnectionName`=?, `adServer`=?, `adDomain`=?, `adUsername`=?, `adBaseDN`=?, `adSecure`=?, `directoryType`=? WHERE `id` = ?";
				
			}
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function updateEndpoint($endpointId, $fullName, $description, $email, $createdBy, $psk = null, $vlan = null, $dacl = null, $expirationDate = null){
			
            $query = "UPDATE endpoints SET fullName=?, description=?, emailAddress=?, vlan=?, dacl=?";
            $queryValues = [$fullName, $description, $email, $vlan, $dacl];
            if ($psk !== null) { $query .= ", pskValue=?"; $queryValues[] = $psk; }
            if ($expirationDate !== null) {
                $query .= ", accountExpired='False', expirationDate=?";
                $queryValues[] = (int)$expirationDate;
            }
            $query .= " WHERE id=?";
            $queryValues[] = (int)$endpointId;
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function updateEndpointPsk($endpointId, $psk){
			
			$queryValues = [$psk, (int)($endpointId)];
			$query = "UPDATE `endpoints` SET `pskValue` = ? WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function updateEndpointVLANdACL($endpointId, $vlan, $dacl){
			
			$queryValues = [$vlan, $dacl, (int)($endpointId)];
			$query = "UPDATE `endpoints` SET `vlan` = ?, `dacl` = ? WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function extendEndpoint($endpointId, $termLengthSeconds, $createdBy){
			
			$queryValues = [(int)($termLengthSeconds), (int)($endpointId)];
			$query = "UPDATE `endpoints` SET `accountExpired` = 'False', `expirationDate` = ? WHERE `id` = ?";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return $queryResult !== false;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function updateExpiredEndpoints(){
			
			$queryValues = [];
			$query = "UPDATE `endpoints` SET `accountExpired` = 'True' WHERE `expirationDate` != 0 AND `expirationDate` < UNIX_TIMESTAMP(NOW())";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}

		function getExpiringEndpoints($days = 5){
			
			$queryValues = [];
			$query = "SELECT macAddress,emailAddress FROM `endpoints` WHERE `expirationDate` != 0 AND (`expirationDate` < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL ? DAY)) AND `expirationDate` > UNIX_TIMESTAMP(NOW()))";
			$queryValues = [(int)$days];

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				if($queryResult){
					if($queryResult->num_rows > 0){
						return $queryResult;
					}
					else {
						return false;
					}
				}
				else {
					return false;
				}
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function resetUserPassword($userId, $encryptedpassword, $createdBy){
			
			$queryValues = [$encryptedpassword, (int)($userId)];
			$query = "UPDATE `internalUsers` SET `password`=? WHERE `id` = ?";

			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function suspendEndpointAssociationbyId($id){
			
			$queryValues = [$id];
			$query = "UPDATE `endpoints` SET `accountEnabled` = 0 WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function activateEndpointAssociationbyId($id){
			
			$queryValues = [$id];
			$query = "UPDATE `endpoints` SET `accountEnabled` = 1 WHERE `id` = ?";
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
		}
		
		function getDashboardStatsbyEPGroup(){
			$queryValues = [];
			$query = 'SELECT endpointGroups.groupName, COUNT(endpointAssociations.macAddress) as endpointCount FROM `endpointAssociations` INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId GROUP BY endpointGroups.id';
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = 'SELECT COUNT(groupName) as endpointGroupCount FROM `endpointGroups`';
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = 'SELECT COUNT(endpointAssociations.macAddress) as endpointCount FROM `endpointAssociations` INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId';
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [time()];
			$query = "SELECT COUNT(endpointAssociations.macAddress) as endpointCount FROM `endpointAssociations` INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId WHERE (endpoints.expirationDate != 0) AND (endpoints.accountExpired = 1 OR endpoints.expirationDate < ?)";
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			$queryValues = [];
			$query = 'SELECT COUNT(macAddress) as unknownEndpointCount FROM `unknownEndpoints`';
			
			$queryResult = $this->preparedQuery($query, $queryValues);
			
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
			
			if (isset($_SESSION['sessionID'])) {
				$session = $_SESSION['sessionID'];
			}
			else {
				$session = "Console Session";
			}

			$queryValues = [$session, $filename, $functionName, $className, $classMethodName, (int)($lineNumber), $message, $payloadData];
			$query = "INSERT INTO `logging` (`sessionID`, `fileName`, `functionName`, `className`,`classMethodName`, `lineNumber`, `message`, `logDataPayload`) VALUES(?,?,?,?,?,?,?,?)";
			
			
			try {
				$queryResult = $this->preparedQuery($query, $queryValues);
				return true;
			}
			catch (Exception $e) {
				//error_log("Caught Exception: $e");
				return false;
			}
			
		}
		
        private function redactLogData($value) {
            if (is_array($value) || is_object($value)) {
                $clean = [];
                foreach ((array)$value as $key => $entry) {
                    $clean[$key] = preg_match('/password|passphrase|psk|preshared|secret|token|cookie|authorization|credential|enc.?key|api.?key/i', (string)$key)
                        ? '[REDACTED]' : $this->redactLogData($entry);
                }
                return $clean;
            }
            if (is_string($value) && preg_match('/^(?:Authorization|Proxy-Authorization|Cookie|Set-Cookie):/i', $value)) return '[REDACTED]';
            return $value;
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
			
				return json_encode($this->redactLogData($logData));
			}else{
				return "";
			}
		}
	}
?>
