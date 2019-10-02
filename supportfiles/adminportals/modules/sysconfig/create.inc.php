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
	
	

	if($sanitizedInput['module-action'] == "hostname" && $sanitizedInput['hostname'] != ""){
		$hostnameId = $ipskISEDB->addHostname($sanitizedInput['hostname'], $_SESSION['logonSID']);
		if($hostnameId){
			print $hostnameId;
		}else{
			print false;
		}
		
	}elseif($sanitizedInput['module-action'] == "protocol" && $sanitizedInput['portalPort'] != "" && $sanitizedInput['protocol'] != ""){

		$protocolPortId = $ipskISEDB->addProtocolPort($sanitizedInput['protocol'], $sanitizedInput['portalPort'], $_SESSION['logonSID']);
		if($protocolPortId){
			print $protocolPortId;
		}else{
			print false;
		}

	}else{
		print false;
	}

?>