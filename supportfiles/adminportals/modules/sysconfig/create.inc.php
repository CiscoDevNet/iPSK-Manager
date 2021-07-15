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