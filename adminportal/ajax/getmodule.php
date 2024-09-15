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
		print "<script>window.location = \"./\"</script>";
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