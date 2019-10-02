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
	include("../../supportfiles/include/config.php");
	include("../../supportfiles/include/iPSKManagerFunctions.php");
	include("../../supportfiles/include/iPSKManagerDatabase.php");
	
	//Optional Components per Page
	include("../../supportfiles/include/BaseRestClass.php");
	include("../../supportfiles/include/CiscoISE-ERS.php");
	
	ipskSessionHandler();

	$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);
	
	$ipskISEDB->set_encryptionKey($encryptionKey);
	$encryptionKey = "";
	
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
		
		$sanitizedInput = sanitizeGetModuleInput($subModuleRegEx);

		if($sanitizedInput['module'] != "" && $sanitizedInput['sub-module'] != ""){
								
			$moduleFileName = "../../supportfiles/adminportals/modules/".$sanitizedInput['module']."/".$sanitizedInput['sub-module'].".inc.php";
			
			if(file_exists($moduleFileName)){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("moduleFileName"=>$moduleFileName), Array("sanitizedInput"=>$sanitizedInput), Array("_POST"=>$_POST));
				$logMessage = "REQUEST:SUCCESS;GET-MODULE:".$sanitizedInput['module'].";SUB-MODULE:".$sanitizedInput['sub-module'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				include($moduleFileName);
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("moduleFileName"=>$moduleFileName), Array("sanitizedInput"=>$sanitizedInput), Array("_POST"=>$_POST));
				$logMessage = "REQUEST:FAILURE[file_not_found];GET-MODULE:".$sanitizedInput['module'].";SUB-MODULE:".$sanitizedInput['sub-module'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header('HTTP/1.0 404 Not Found', true, 404);
			}

		}elseif($sanitizedInput['sub-module'] == "" && isset($_POST['sub-module'])){
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("moduleFileName"=>$moduleFileName), Array("sanitizedInput"=>$sanitizedInput), Array("_POST"=>$_POST));
			$logMessage = "REQUEST:FAILURE[invalid_sub-module];GET-MODULE:".$sanitizedInput['module'].";SUB-MODULE:".$sanitizedInput['sub-module'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			header('HTTP/1.0 404 Not Found', true, 404);
		}elseif($sanitizedInput['module'] != "" && $sanitizedInput['sub-module'] == ""){
					
			$moduleFileName = "../../supportfiles/adminportals/modules/".$sanitizedInput['module']."/".$sanitizedInput['module'].".inc.php";
			
			if(file_exists($moduleFileName)){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("moduleFileName"=>$moduleFileName), Array("sanitizedInput"=>$sanitizedInput), Array("_POST"=>$_POST));
				$logMessage = "REQUEST:SUCCESS;GET-MODULE:".$sanitizedInput['module'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				include($moduleFileName);
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("moduleFileName"=>$moduleFileName), Array("sanitizedInput"=>$sanitizedInput), Array("_POST"=>$_POST));
				$logMessage = "REQUEST:FAILURE[invalid_module];GET-MODULE:".$sanitizedInput['module'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header('HTTP/1.0 404 Not Found', true, 404);
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("moduleFileName"=>$moduleFileName), Array("sanitizedInput"=>$sanitizedInput), Array("_POST"=>$_POST));
			$logMessage = "REQUEST:FAILURE[invalid_module];GET-MODULE:".$sanitizedInput['module'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			header('HTTP/1.0 404 Not Found', true, 404);
		}
	}
?>