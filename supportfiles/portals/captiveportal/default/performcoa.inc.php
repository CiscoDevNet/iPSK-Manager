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