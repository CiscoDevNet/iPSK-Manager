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
		
	ipskSessionHandler();
	
	$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);
	
	//START-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	$encryptionKey = "";
	$dbPassword = "";
	unset($encryptionKey);
	unset($dbPassword);
	//END-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE

	$sanitizedInput = sanitizeGetModuleInput($subModuleRegEx);
	
	if(isset($sanitizedInput['logoff'])){	
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:SUCCESS;ACTION:ADMINLOGOFF;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USER:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$_SESSION = null;
		session_destroy();
		header("Location: /");
		die();
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE[invalid_input];ACTION:ADMINLOGOFF;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USER:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: /adminportal.php");
		die();	
	}

?>