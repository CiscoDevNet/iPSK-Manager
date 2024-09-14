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

	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: index.php?portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}");
		die();
	}
	
	if(isset($sessionData['portalGET']['client_mac'])){
		$clientMac = str_replace("-",":",$_SESSION['portalGET']['client_mac']);
	}

	if($iseMNTIntegrationAvailable && isset($clientMac)){
		//Get ISE MnT Persona Hostname from URL provided by Administrator
		$mntHostname = $ipskISEMNT->getISEMntHostname();
		
		//Perform CoA against the MnT persona with the MAC address of the device
		$outputData	= $ipskISEMNT->invokeSessionCoADisconnectShutdown($mntHostname,$clientMac);
		
		print $outputData;
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("clientMac"=>$clientMac), Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE[integration_disabled_or_mac_missing];ACTION:ACTIONPORTAL-COA;CLIENT-MAC:clientMac;MNT-ENABLED:$iseMNTIntegrationAvailable;HOSTNAME:".$_SERVER['SERVER_NAME'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
	}
?>