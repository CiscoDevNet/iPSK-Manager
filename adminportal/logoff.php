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
		header("Location: ./");
		die();
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE[invalid_input];ACTION:ADMINLOGOFF;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USER:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: adminportal.php");
		die();	
	}

?>