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

//print_r($sanitizedInput);
	if($sanitizedInput['module-action'] == "general"){
		$ipskISEDB->setGlobalSetting("admin-portal","admin-portal-hostname", $sanitizedInput['adminPortalHostname']);
		$ipskISEDB->setGlobalSetting("admin-portal","admin-portal-strict-hostname", $sanitizedInput['strict-hostname']);
		$ipskISEDB->setGlobalSetting("admin-portal","redirect-on-hostname-match", $sanitizedInput['redirect-hostname']);
		print true;
	}elseif($sanitizedInput['module-action'] == "ersupdate"){
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","enabled", $sanitizedInput['ersEnabled']);
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","verify-ssl-peer", $sanitizedInput['ersVerifySsl']);
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","ersHost", $sanitizedInput['ersHost']);
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","ersUsername", $sanitizedInput['ersUsername']);
		print true;
	}elseif($sanitizedInput['module-action'] == "erspass"){	
		$ipskISEDB->setISEERSPassword($sanitizedInput['ersPassword']);
		print true;		
	}elseif($sanitizedInput['module-action'] == "mntupdate"){
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","enabled", $sanitizedInput['mntEnabled']);
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","verify-ssl-peer", $sanitizedInput['mntVerifySsl']);
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","mntHost", $sanitizedInput['mntHost']);
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","mntUsername", $sanitizedInput['mntUsername']);
		print true;
	}elseif($sanitizedInput['module-action'] == "mntpass"){	
		$ipskISEDB->setISEMnTPassword($sanitizedInput['mntPassword']);
		print true;
	}elseif($sanitizedInput['module-action'] == "smtpupdate"){
		$ipskISEDB->setGlobalSetting("smtp-settings","enabled", $sanitizedInput['smtpEnabled']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-hostname", $sanitizedInput['smtpHost']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-port", $sanitizedInput['smtpPort']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-username", $sanitizedInput['smtpUsername']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-fromaddress", $sanitizedInput['smtpFromAddress']);
		print true;
	}elseif($sanitizedInput['module-action'] == "smtppass"){	
		$ipskISEDB->setSMTPPassword($sanitizedInput['smtpPassword']);
		print true;
	}elseif($sanitizedInput['module-action'] == "advancedupdate"){
		$ipskISEDB->setGlobalSetting("advanced-settings","enable-portal-psk-edit", $sanitizedInput['portalPskEditEnabled']);
		print true;
	}else{
		print false;
	}
?>