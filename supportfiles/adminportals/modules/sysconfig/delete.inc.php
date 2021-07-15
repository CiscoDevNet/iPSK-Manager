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