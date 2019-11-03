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
	
	//Clear Variables and set to blank
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: /index.php?portalId=".$portalId);
		die();
	}
	
	$sampleFile = (isset($_GET['samplefile'])) ? (filter_var($_GET['samplefile'],FILTER_VALIDATE_BOOLEAN)) : false;
	
	$sampleCSV = "macaddress,fullname,emailaddress,description\r\n00:00:00:FF:FF:FF,Sample Name,Sample@Demo.Local,My Device - Mobile Phone";
	
	if(isset($sanitizedInput['action'])) {	
		if($sanitizedInput['action'] == "get_endpoint_groups"){
			if($iseERSIntegrationAvailable){
				print $ipskISEERS->getEndPointIdentityGroups();
			}					
		}elseif($sanitizedInput['action'] == "get_endpoint_count"){
			if($iseERSIntegrationAvailable){
				print $ipskISEERS->getEndPointGroupCountbyId($sanitizedInput['groupUuid']);
			}
		}elseif($sanitizedInput['action'] == "get_random_psk"){
			$authZ = $ipskISEDB->getEndPointAuthorizationPolicy($sanitizedInput['id']);
			
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:SUCCESS;GET-DATA-COMMAND:".$sanitizedInput['action'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			$psk = $ipskISEDB->generateRandomPassword($authZ['pskLength']);
			
			$_SESSION['temp']['sponsoreditpsk'] = password_hash($psk, PASSWORD_DEFAULT);
			
			print $psk;
		}
	}else{
		if($sampleFile == true){
			header('Content-Description: File Transfer');
			header('Content-Type: plain/text');
			header('Content-Disposition: attachment; filename=import_sample.csv'); 
			header('Content-Transfer-Encoding: text');
			header('Content-Length: '.strlen($sampleCSV));
			echo $sampleCSV;
		}
	}
?>