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
		
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	//Core Components
	include("../supportfiles/include/config.php");
	include("../supportfiles/include/iPSKManagerFunctions.php");
	include("../supportfiles/include/iPSKManagerDatabase.php");
	
	//Optional Components per Page
	include("../supportfiles/include/BaseLDAPClass.php");
	
	ipskSessionHandler();
	
	if(isset($_POST['logoff'])){
		$_SESSION = null;
		session_destroy();
		header("Location: /");
	}
	
	$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);
	
	$ipskISEDB->set_encryptionKey($encryptionKey);
	
	//START-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	$encryptionKey = "";
	$dbPassword = "";
	unset($encryptionKey);
	unset($dbPassword);
	//END-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	
	//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $_POST
	$inputPassword = (isset($_POST['inputPassword'])) ? $_POST['inputPassword'] : '';
	unset($_POST["inputPassword"]);
	//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $_POST
	
	//System Sid Variable
	$systemSID = $baseSid."-".$orgSid."-".$systemSid;

	$sanitizedInput = sanitizeGetModuleInput($subModuleRegEx);

	$samlSettings = $ipskISEDB->getGlobalClassSetting("saml-settings");
	$samlLogin = (isset($samlSettings['enabled'])) ? $samlSettings['enabled'] : false;
	
	if ($samlLogin) {
		$samlUsernameField = ($samlSettings['usernamefield'] != '') ? $samlSettings['usernamefield'] : 'REMOTE_USER';
	}
	
	if ($samlLogin == true && $samlSettings['headers'] == true) {
		$requestHeaders = getallheaders();
		$samlUsername = (isset($requestHeaders[$samlUsernameField])) ? $requestHeaders[$samlUsernameField] : '';
		$sanitizedInput["inputUsername"] = $samlUsername;
		$inputPassword = 'bogus';
	} elseif ($samlLogin == true) {
		$samlUsername = (isset($_SERVER[$samlUsernameField])) ? $_SERVER[$samlUsernameField] : '';
		$sanitizedInput["inputUsername"] = $samlUsername;
		$inputPassword = 'bogus';
	}

	if ($samlLogin == true && $samlSettings['ldap-source'] == true) {
		$sanitizedInput['authDirectory'] = $samlSettings['ldap-source-directory'];
	} elseif ($samlLogin == true) {
		$sanitizedInput['authDirectory'] = 0;
	}
	
	//LOG::Entry
	$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
	$logMessage = "REQUEST:SUCCESS;ACTION:ADMINLOGIN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
	$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
	
	if($sanitizedInput["inputUsername"] != "" && $inputPassword != "" && is_numeric($sanitizedInput["authDirectory"])){
		if($sanitizedInput['authDirectory'] == "0"){
			if($ipskISEDB->authenticateInternalUser($sanitizedInput["inputUsername"], $inputPassword, $samlLogin)){
								
				$authorizedGroups = $ipskISEDB->getPortalAdminGroups();
				
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:SUCCESS;ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				if($authorizedGroups['count'] > 0){
					for($count = 0; $count < $authorizedGroups['count']; $count++){
						for($userCount = 0; $userCount < $_SESSION['memberOf']['count']; $userCount++){
							if($authorizedGroups[$count] == $_SESSION['memberOf'][$userCount]){
								$_SESSION['authorizationGroup'] = $authorizedGroups[$count];
								$_SESSION['authorizationGranted'] = true;
								$_SESSION['authorizationTimestamp'] = time();
								
								//LOG::Entry
								$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("sanitizedInput"=>$sanitizedInput));
								$logMessage = "REQUEST:SUCCESS;ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
								$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
								
								$ipskISEDB->addUserCacheEntry($_SESSION['logonSID'],$_SESSION['userPrincipalName'],$_SESSION['sAMAccountName'],$_SESSION['logonDN'], $systemSID);
								$adminSettings = $ipskISEDB->getGlobalClassSetting("admin-portal");
								if(isset($adminSettings['log-purge-interval']) && $adminSettings['log-purge-interval'] != '') {
									$ipskISEDB->purgeLogs($adminSettings['log-purge-interval']);
								}
								header("Location: adminportal.php");
								die();
							}
						}
					}
					
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE{1}[user_authz_failure];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					header("Location: index.php?error=1");
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE{2}[no_authz_groups];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					header("Location: index.php?error=2");
				}	
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE{3}[user_authn_failure];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
				header("Location: index.php?error=3");
			}
		}else{
			if(is_numeric($sanitizedInput["authDirectory"])){
				if($ipskISEDB->getLdapDirectoryCount() > 0){
					$ldapCreds = $ipskISEDB->getLdapSettings($sanitizedInput["authDirectory"]);
					$ldapSettings = $ipskISEDB->getGlobalClassSetting("ldap-settings");

					if($ldapCreds){
						$ldapClass = New BaseLDAPInterface($ldapCreds['adServer'], $ldapCreds['adDomain'], $ldapCreds['adUsername'], $ldapCreds['adPassword'], $ldapCreds['adBaseDN'], $ldapCreds['adSecure'], $ldapSettings['ldap-ssl-check'], $ldapCreds['directoryType'], $ipskISEDB);
						//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
						unset($ldapCreds['adPassword']);
						//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
					
						$authorizedGroups = $ipskISEDB->getPortalAdminGroups();

						$validUser = $ldapClass->authenticateUser($sanitizedInput["inputUsername"], $inputPassword, $samlLogin, $ldapSettings['nested-groups']);

						if($validUser){
							//LOG::Entry
							$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
							$logMessage = "REQUEST:SUCCESS;ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
							$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
							
							if($authorizedGroups['count'] > 0){
								for($count = 0; $count < $authorizedGroups['count']; $count++){
									for($userCount = 0; $userCount < $_SESSION['memberOf']['count']; $userCount++){
										if($authorizedGroups[$count] == $_SESSION['memberOf'][$userCount]){
											$_SESSION['authorizationGroup'] = $authorizedGroups[$count];
											$_SESSION['authorizationGranted'] = true;
											$_SESSION['authorizationTimestamp'] = time();
											
											//LOG::Entry
											$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
											$logMessage = "REQUEST:SUCCESS;ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
											$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
											
											$ipskISEDB->addUserCacheEntry($_SESSION['logonSID'],$_SESSION['userPrincipalName'],$_SESSION['sAMAccountName'],$_SESSION['logonDN'], $systemSID);
											$adminSettings = $ipskISEDB->getGlobalClassSetting("admin-portal");
											if($adminSettings['log-purge-interval'] != '') {
												$ipskISEDB->purgeLogs($adminSettings['log-purge-interval']);
											}
											header("Location: adminportal.php");
											die();
										}
									}
								}
								//LOG::Entry
								$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
								$logMessage = "REQUEST:FAILURE{1}[user_authz_failure];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";".$authorizedGroups['count'].";";
								$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
								header("Location: index.php?error=1");
								
							}else{
								//LOG::Entry
								$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
								$logMessage = "REQUEST:FAILURE{2}[no_authz_groups];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
								$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
											
								header("Location: index.php?error=2");
							}					
						}else{
							//LOG::Entry
							$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
							$logMessage = "REQUEST:FAILURE{3}[user_authn_failure];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
							$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
							
							header("Location: index.php?error=3");
						}
					}else{
						//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
						unset($ldapCreds['adPassword']);
						//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
						
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
						$logMessage = "REQUEST:FAILURE{4}[invalid_ldap_directory];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						header("Location: index.php?error=4");
					}
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE{5}[no_valid_auth_directories];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					header("Location: index.php?error=5");
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE{6}[invalid_auth_directory_input];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header("Location: index.php?error=6");
			}
		}
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE{7}[invalid_form_input];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: index.php?error=7");
	}
?>