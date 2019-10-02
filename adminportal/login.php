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
	
	//LOG::Entry
	$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
	$logMessage = "REQUEST:SUCCESS;ACTION:ADMINLOGIN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
	$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
	
	if($sanitizedInput["inputUsername"] != "" && $inputPassword != "" && is_numeric($sanitizedInput["authDirectory"])){
		if($sanitizedInput['authDirectory'] == "0"){
			if($ipskISEDB->authenticateInternalUser($sanitizedInput["inputUsername"], $inputPassword)){
								
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
								header("Location: /adminportal.php");
								die();
							}
						}
					}
					
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE{1}[user_authz_failure];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					header("Location: /index.php?error=1");
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE{2}[no_authz_groups];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					header("Location: /index.php?error=2");
				}	
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE{3}[user_authn_failure];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
				header("Location: /index.php?error=3");
			}
		}else{
			if(is_numeric($sanitizedInput["authDirectory"])){
				if($ipskISEDB->getLdapDirectoryCount() > 0){
					$ldapCreds = $ipskISEDB->getLdapSettings($sanitizedInput["authDirectory"]);
					
					if($ldapCreds){
						$ldapClass = New BaseLDAPInterface($ldapCreds['adServer'], $ldapCreds['adDomain'], $ldapCreds['adUsername'], $ldapCreds['adPassword'], $ldapCreds['adBaseDN'], $ldapCreds['adSecure'], $ipskISEDB);
						//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
						unset($ldapCreds['adPassword']);
						//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
					
						$authorizedGroups = $ipskISEDB->getPortalAdminGroups();

						$validUser = $ldapClass->authenticateUser($sanitizedInput["inputUsername"], $inputPassword);
											
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
											header("Location: /adminportal.php");
											die();
										}
									}
								}
								//LOG::Entry
								$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
								$logMessage = "REQUEST:FAILURE{1}[user_authz_failure];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
								$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
								header("Location: /index.php?error=1");
								
							}else{
								//LOG::Entry
								$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
								$logMessage = "REQUEST:FAILURE{2}[no_authz_groups];ACTION:ADMINAUTHZ;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
								$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
											
								header("Location: /index.php?error=2");
							}					
						}else{
							//LOG::Entry
							$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
							$logMessage = "REQUEST:FAILURE{3}[user_authn_failure];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
							$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
							
							header("Location: /index.php?error=3");
						}
					}else{
						//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
						unset($ldapCreds['adPassword']);
						//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
						
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
						$logMessage = "REQUEST:FAILURE{4}[invalid_ldap_directory];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						header("Location: /index.php?error=4");
					}
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE{5}[no_valid_auth_directories];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					header("Location: /index.php?error=5");
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE{6}[invalid_auth_directory_input];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header("Location: /index.php?error=6");
			}
		}
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE{7}[invalid_form_input];ACTION:ADMINAUTHN;USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: /index.php?error=7");
	}
?>