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
	
	$dataCommandRegEx = "/^(?:getdata|test|generate|validate)$/";
	
	$dataDataSetRegEx = "/^(?:internalgroups|ldap|psk|authzprofile|get_endpoint_groups|get_endpoint_count)$/";
	
	$dataInputTypeRegEx = "/^(?:id|groupUuid)$/";
		
	//Core Components
	include("../../supportfiles/include/config.php");
	include("../../supportfiles/include/iPSKManagerFunctions.php");
	include("../../supportfiles/include/iPSKManagerDatabase.php");
	
	//Optional Components per Page
	include("../../supportfiles/include/BaseRestClass.php");
	include("../../supportfiles/include/CiscoISE-MnT.php");
	include("../../supportfiles/include/CiscoISE-ERS.php");
	include("../../supportfiles/include/BaseLDAPClass.php");
	
	ipskSessionHandler();

	$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);
	
	$ipskISEDB->set_encryptionKey($encryptionKey);
	$encryptionKey = "";
	
	$sampleFile = (isset($_GET['samplefile'])) ? (filter_var($_GET['samplefile'],FILTER_VALIDATE_BOOLEAN)) : false;
	
	if(!ipskLoginSessionCheck()){
		session_destroy();
		print "<script>window.location = \"/\"</script>";
	}else{
		$ersCreds = $ipskISEDB->getISEERSSettings();
	
		if($ersCreds['enabled']){
					
			if(!isset($ersCreds['verify-ssl-peer'])){
				$ersCreds['verify-ssl-peer'] = true;
			}
			
			$ipskISEERS = new CiscoISEERSRestAPI($ersCreds['ersHost'], $ersCreds['ersUsername'], $ersCreds['ersPassword'], $ersCreds['verify-ssl-peer'], $ipskISEDB);
			$ersCreds = "";
			
			$iseERSIntegrationAvailable = true;
		}else{
			$iseERSIntegrationAvailable = false;
		}
		
		$dataInputFilter = FILTER_VALIDATE_INT;
		
		$sanitizedInput = sanitizeGetDataInput($dataCommandRegEx, $dataDataSetRegEx, "id", $dataInputFilter);

		if($sanitizedInput['data-command'] == "getdata" && $sanitizedInput['data-set'] == "internalgroups"){
						
			$internalGroups = $ipskISEDB->getInternalGroups($sanitizedInput['id']);
			if($internalGroups->num_rows != 0){
				while($row = $internalGroups->fetch_assoc()){
					$dataSet[$row['id']] = $row['groupName'];
				}
				$jsonData = json_encode($dataSet,true);
			}else{
				$jsonData = '{""}';
			}
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("jsonData"=>$jsonData), Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:SUCCESS;GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			print $jsonData;
		}else if($sanitizedInput['data-command'] == "test" && $sanitizedInput['data-set'] == "ldap"){
			$ldapCreds = $ipskISEDB->getLdapSettings($sanitizedInput['id']);
			
			if($ldapCreds){
				$ldapClass = New BaseLDAPInterface($ldapCreds['adServer'], $ldapCreds['adDomain'], $ldapCreds['adUsername'], $ldapCreds['adPassword'], $ldapCreds['adBaseDN'], $ldapCreds['adSecure']);
				unset($ldapCreds['adPassword']);
			
				if($ldapClass->testLdapServer()){
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:SUCCESS;GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					print "LDAP Connection - Successful";
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE[ldap_test_failure];GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					print "LDAP Connection - Failure";
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("ldapCreds"=>$ldapCreds), Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[ldap_credentials_missing];GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				print "LDAP Connection - Failure";
			}
		}else if($sanitizedInput['data-command'] == "generate" && $sanitizedInput['data-set'] == "psk"){
			if(!is_numeric($sanitizedInput['pskLength'])){
				$pskLength = 8;
			}else{
				$pskLength = $sanitizedInput['pskLength'];
			}
			
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:SUCCESS;GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			$psk = $ipskISEDB->generateRandomPassword($pskLength);
			
			print $psk;
		}else if($sanitizedInput['data-command'] == "validate" && $sanitizedInput['data-set'] == "authzprofile"){
			//Check if Authorization Profile Exists
			if($iseERSIntegrationAvailable){
				$result = $ipskISEERS->check_ifAuthZProfileExists($sanitizedInput['authzProfileName']);
				
				if($result){
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE[authz_profile_exists];GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";AUTHZ-PROFILE-NAME:".$sanitizedInput['authzProfileName'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					print "exists";
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:SUCCESS;GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";AUTHZ-PROFILE-NAME:".$sanitizedInput['authzProfileName'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					print "not_exists";
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[ise_ers_integration_disabled];GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				print "no_integration";
			}
		}else if($sanitizedInput['data-command'] == "getdata" && $sanitizedInput['data-set'] == "get_endpoint_groups"){
			if($iseERSIntegrationAvailable){
				$endpointIdentityGroups = $ipskISEERS->getEndPointIdentityGroups();

				if($endpointIdentityGroups){
					$endpointIdentityGroupsArray = json_decode($endpointIdentityGroups,TRUE);
					$endpointIdentityGroupsArray = arraySortAlpha($endpointIdentityGroupsArray);
					$endpointIdentityGroups = json_encode($endpointIdentityGroupsArray);

					print $endpointIdentityGroups;
				}
			}
		}else if($sanitizedInput['data-command'] == "getdata" && $sanitizedInput['data-set'] == "get_endpoint_count"){
			if($iseERSIntegrationAvailable){
				print $ipskISEERS->getEndPointGroupCountbyId($sanitizedInput['groupUuid']);
			}
		}else if($sampleFile == true){
			$sampleCSV = "macaddress,fullname,emailaddress,description\r\n00:00:00:FF:FF:FF,Sample Name,Sample@Demo.Local,My Device - Mobile Phone";
			
			header('Content-Description: File Transfer');
			header('Content-Type: plain/text');
			header('Content-Disposition: attachment; filename=import_sample.csv'); 
			header('Content-Transfer-Encoding: text');
			header('Content-Length: '.strlen($sampleCSV));
			echo $sampleCSV;

		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:UNKNOWN[invalid_input_parameters];GET-DATA-COMMAND:".$sanitizedInput['data-command'].";DATA-SET:".$sanitizedInput['data-set'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			header('HTTP/1.0 404 Not Found', true, 404);
		}
	}
	
?>