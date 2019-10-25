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
	
	
	//Perform Login
	$_SESSION['portalAuthorization']['viewall'] = false;
	$_SESSION['portalAuthorization']['viewallDn'] = "";
	$_SESSION['portalAuthorization']['viewgroup'] = false;
	$_SESSION['portalAuthorization']['viewowned'] = false;
	$_SESSION['portalAuthorization']['create'] = false;
	$_SESSION['portalAuthorization']['bulkcreate'] = false;
	$_SESSION['portalAuthorization']['viewallPSK'] = false;
	$_SESSION['portalAuthorization']['viewgroupPSK'] = false;
	$_SESSION['portalAuthorization']['viewownedPSK'] = false;
	
	$authCreate = false;
	$bulkCreate = false;
	$authViewAll = false;
	$authViewAllDn = ""; 
	$authViewGroup = false;
	$authViewOwned = false;
	$authViewAllPSK = false;
	$authViewGroupPSK = false;
	$authViewOwnedPSK = false;
	
	//System Sid Variable
	$systemSID = $baseSid."-".$orgSid."-".$systemSid;
	
	//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $_POST
	$inputPassword = (isset($_POST['inputPassword'])) ? $_POST['inputPassword'] : '';
	unset($_POST["inputPassword"]);
	//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $_POST
	
	if($sanitizedInput["inputUsername"] != "" && $inputPassword != ""){
		if($_SESSION['portalSettings']['authenticationDirectory'] == "0"){
			if($ipskISEDB->authenticateInternalUser($sanitizedInput["inputUsername"],$inputPassword)){
				$authorizedGroups = $ipskISEDB->getPortalAuthGroups($_SESSION['portalSettings']['id']);
				
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORAUTHN;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USER:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				$matchedGroupCount = 0;
				
				if($authorizedGroups['count'] > 0){
					$groupCount = 0;
					for($count = 0; $count < $authorizedGroups['count']; $count++){
						for($userCount = 0; $userCount < $_SESSION['memberOf']['count']; $userCount++){
							if($authorizedGroups[$count]['groupDn'] == $_SESSION['memberOf'][$userCount]){
								
								if(($authorizedGroups[$count]['groupPermissions'] & 2048) == 2048) { $bulkCreate = true; }
								if(($authorizedGroups[$count]['groupPermissions'] & 512) == 512) { $authCreate = true; }
								if(($authorizedGroups[$count]['groupPermissions'] & 12) == 12) { $authViewAllPSK = true; }
								if(($authorizedGroups[$count]['groupPermissions'] & 4) == 4) { $authViewAll = true; $authViewAllDn = $authorizedGroups[$count]['groupDn']; }
								if(($authorizedGroups[$count]['groupPermissions'] & 2) == 2 ) { $authViewGroup = true; }
								if(($authorizedGroups[$count]['groupPermissions'] & 1) == 1 ) { $authViewOwned = true; }
								
								$_SESSION['authorizationGroups'][$matchedGroupCount]['groupDn'] = $authorizedGroups[$count]['groupDn'];
								$_SESSION['authorizationGroups'][$matchedGroupCount]['groupPermissions'] = $authorizedGroups[$count]['groupPermissions'];
									
								$matchedGroupCount++;
								$authZSuccess = true;
							}
						}
					}
					$_SESSION['authorizationGroups']['count'] = $matchedGroupCount;
					
					$_SESSION['portalAuthorization']['create'] = $authCreate;
					$_SESSION['portalAuthorization']['bulkcreate'] = $bulkCreate;
					$_SESSION['portalAuthorization']['viewall'] = $authViewAll;
					$_SESSION['portalAuthorization']['viewallDn'] = $authViewAllDn;
					$_SESSION['portalAuthorization']['viewallPSK'] = $authViewAllPSK;
					$_SESSION['portalAuthorization']['viewgroup'] = $authViewGroup;
					$_SESSION['portalAuthorization']['viewgroupPSK'] = $authViewGroupPSK;
					$_SESSION['portalAuthorization']['viewowned'] = $authViewOwned;
					$_SESSION['portalAuthorization']['viewownedPSK'] = $authViewOwnedPSK;
							
					if($authZSuccess){					
						$_SESSION['authorizationGranted'] = true;
						$_SESSION['authorizationTimestamp'] = time();
						$ipskISEDB->addUserCacheEntry($_SESSION['logonSID'],$_SESSION['userPrincipalName'],$_SESSION['sAMAccountName'],$_SESSION['logonDN'], $systemSID);
						
						if(!isset($_SESSION['authorizedEPGroups'])){
							$_SESSION['authorizedEPGroups'] = $ipskISEDB->getEndPointGroupAuthorizations($_SESSION['portalSettings']['id'],$_SESSION['authorizationGroups']);
							$_SESSION['authorizedWirelessNetworks'] = $ipskISEDB->getPortalWirelessNetworkAuthorization($_SESSION['portalSettings']['id'],$_SESSION['authorizationGroups']);
						}
						
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
						$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";SID:".$_SESSION['logonSID'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						if($_SESSION['portalAuthorization']['create'] == false){
							header("Location: /manage.php?portalId=".$portalId);
							die();
						}else{
							header("Location: /sponsor.php?portalId=".$_SESSION['portalSettings']['portalId']);
							die();
						}
					}else{
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("authorizationGroups"=>$_SESSION['authorizationGroups']));
						$logMessage = "REQUEST:FAILURE{1}[user_authz_failure];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USER:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						header("Location: /index.php?error=1&portalId=".$_SESSION['portalSettings']['portalId']);
					}
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("authorizationGroups"=>$_SESSION['authorizationGroups']));
					$logMessage = "REQUEST:FAILURE{2}[no_authz_groups];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USER:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					
					header("Location: /index.php?error=2&portalId=".$_SESSION['portalSettings']['portalId']);
				}	
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("authorizationGroups"=>$_SESSION['authorizationGroups']));
				$logMessage = "REQUEST:FAILURE{3}[user_authn_failure];ACTION:SPONSORAUTHN;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USER:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header("Location: /index.php?error=3&portalId=".$_SESSION['portalSettings']['portalId']);
			}
		}else{
			if(is_numeric($_SESSION['portalSettings']['id'])){
	
				$ldapCreds = $ipskISEDB->getLdapSettings($_SESSION['portalSettings']['authenticationDirectory']);
				
				if($ldapCreds){
				
					$authorizedGroups = $ipskISEDB->getPortalAuthGroups($_SESSION['portalSettings']['id']);
					
					$ldapClass = New BaseLDAPInterface($ldapCreds['adServer'], $ldapCreds['adDomain'], $ldapCreds['adUsername'], $ldapCreds['adPassword'], $ldapCreds['adBaseDN'], $ldapCreds['adSecure'], $ipskISEDB);

					//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
					unset($ldapCreds['adPassword']);
					//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds					
					
					$validUser = $ldapClass->authenticateUser($sanitizedInput["inputUsername"], $inputPassword);
					
					$matchedGroupCount = 0;
					
					if($validUser){
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
						$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORAUTHN;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";SID:".$_SESSION['logonSID'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						if($authorizedGroups['count'] > 0){
							for($count = 0; $count < $authorizedGroups['count']; $count++){
								for($userCount = 0; $userCount < $_SESSION['memberOf']['count']; $userCount++){
									if(strtolower($authorizedGroups[$count]['groupDn']) == strtolower($_SESSION['memberOf'][$userCount])){
										
										if(($authorizedGroups[$count]['groupPermissions'] & 2048) == 2048) { $bulkCreate = true; }
										if(($authorizedGroups[$count]['groupPermissions'] & 512) == 512) { $authCreate = true; }
										if(($authorizedGroups[$count]['groupPermissions'] & 12) == 12) { $authViewAllPSK = true; }
										if(($authorizedGroups[$count]['groupPermissions'] & 4) == 4) { $authViewAll = true; $authViewAllDn = $authorizedGroups[$count]['groupDn']; }
										if(($authorizedGroups[$count]['groupPermissions'] & 2) == 2 ) { $authViewGroup = true; }
										if(($authorizedGroups[$count]['groupPermissions'] & 1) == 1 ) { $authViewOwned = true; }													
										
										$_SESSION['authorizationGroups'][$matchedGroupCount]['groupDn'] = $authorizedGroups[$count]['groupDn'];
										$_SESSION['authorizationGroups'][$matchedGroupCount]['groupPermissions'] = $authorizedGroups[$count]['groupPermissions'];
										$matchedGroupCount++;
										$authZSuccess = true;
									}
								}
							}
							
							$_SESSION['authorizationGroups']['count'] = $matchedGroupCount;
							
							$_SESSION['portalAuthorization']['create'] = $authCreate;
							$_SESSION['portalAuthorization']['bulkcreate'] = $bulkCreate;
							$_SESSION['portalAuthorization']['viewall'] = $authViewAll;
							$_SESSION['portalAuthorization']['viewallDn'] = $authViewAllDn;
							$_SESSION['portalAuthorization']['viewallPSK'] = $authViewAllPSK;
							$_SESSION['portalAuthorization']['viewgroup'] = $authViewGroup;
							$_SESSION['portalAuthorization']['viewgroupPSK'] = $authViewGroupPSK;
							$_SESSION['portalAuthorization']['viewowned'] = $authViewOwned;
							$_SESSION['portalAuthorization']['viewownedPSK'] = $authViewOwnedPSK;
							
							if($authZSuccess){		
								$_SESSION['authorizationGranted'] = true;
								$_SESSION['authorizationTimestamp'] = time();
								$ipskISEDB->addUserCacheEntry($_SESSION['logonSID'],$_SESSION['userPrincipalName'],$_SESSION['sAMAccountName'],$_SESSION['logonDN'], $systemSID);
								
								if(!isset($_SESSION['authorizedEPGroups'])){
									$_SESSION['authorizedEPGroups'] = $ipskISEDB->getEndPointGroupAuthorizations($_SESSION['portalSettings']['id'],$_SESSION['authorizationGroups']);
									$_SESSION['authorizedWirelessNetworks'] = $ipskISEDB->getPortalWirelessNetworkAuthorization($_SESSION['portalSettings']['id'],$_SESSION['authorizationGroups']);
								}
								
								//LOG::Entry
								$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
								$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";SID:".$_SESSION['logonSID'].";";
								$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
								
								if($_SESSION['portalAuthorization']['create'] == false){
									header("Location: /manage.php?portalId=".$portalId);
									die();
								}else{
									header("Location: /sponsor.php?portalId=".$_SESSION['portalSettings']['portalId']);
									die();
								}
							}else{
								//LOG::Entry
								$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
								$logMessage = "REQUEST:FAILURE{1}[user_authz_failure];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
								$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
								
								header("Location: /index.php?error=1&portalId=".$_SESSION['portalSettings']['portalId']);
							}
						}else{
							//LOG::Entry
							$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
							$logMessage = "REQUEST:FAILURE{2}[no_authz_groups];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
							$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
							
							header("Location: /index.php?error=2&portalId=".$_SESSION['portalSettings']['portalId']);
						}					
					}else{
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
						$logMessage = "REQUEST:FAILURE{3}[user_authn_failure];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						header("Location: /index.php?error=3&portalId=".$_SESSION['portalSettings']['portalId']);
					}
				}else{
					//START-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
					unset($ldapCreds['adPassword']);
					//END-[DO NOT REMOVE] - REMOVES PASSWORD FROM $ldapCreds
					
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE{4}[invalid_ldap_directory];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					header("Location: /index.php?error=4&portalId=".$_SESSION['portalSettings']['portalId']);
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE{5}[no_valid_auth_directories];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header("Location: /index.php?error=5&portalId=".$_SESSION['portalSettings']['portalId']);
			}
		}
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("authorizedGroups"=>$authorizedGroups), Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE{7}[user_authz_failure];ACTION:SPONSORAUTHZ;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: /index.php?error=7&portalId=".$_SESSION['portalSettings']['portalId']);
	}
?>