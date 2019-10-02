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
	
	
	if($sanitizedInput['module-action'] == "hostname" && is_array($_POST['id'])){
		$temp = $_POST['id'];
		$sanitizedHostnames = filter_var_array($temp,FILTER_VALIDATE_INT);

		$hostnameResult = $ipskISEDB->deleteHostnameById($sanitizedHostnames, $_SESSION['logonSID']);
		
		print $hostnameResult;
	}elseif( $sanitizedInput['module-action'] == "protocol" && is_array($_POST['id'])){
		$temp = $_POST['id'];
		$sanitizedPortalProtocols = filter_var_array($temp,FILTER_VALIDATE_INT);
		
		$protocolResult = $ipskISEDB->deleteProtocolPortById($sanitizedPortalProtocols, $_SESSION['logonSID']);
		
		print $protocolResult;
	}

?>