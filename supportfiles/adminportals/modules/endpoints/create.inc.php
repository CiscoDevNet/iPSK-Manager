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
	
	
	
	$htmlbody = <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'endpoints'
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#mainContent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
</script>
HTML;

	if(isset($sanitizedInput['associationGroup']) && isset($sanitizedInput['macAddress']) && isset($sanitizedInput['endpointDescription']) && isset($sanitizedInput['emailAddress']) && isset($sanitizedInput['fullName'])) {	
		$endpointGroupAuthorization = $ipskISEDB->getAuthorizationTemplatesbyEPGroupId($sanitizedInput['associationGroup']);
		
		if($endpointGroupAuthorization['ciscoAVPairPSK'] == "*devicerandom*"){
			$randomPassword = $ipskISEDB->generateRandomPassword($endpointGroupAuthorization['pskLength']);
			$randomPSK = "psk=".$randomPassword;
		}elseif($endpointGroupAuthorization['ciscoAVPairPSK'] == "*userrandom*"){
			$userPsk = $ipskISEDB->getUserPreSharedKey($sanitizedInput['associationGroup'],$_SESSION['logonSID']);
			if(!$userPsk){
				$randomPassword = $ipskISEDB->generateRandomPassword($endpointGroupAuthorization['pskLength']);
				$randomPSK = "psk=".$randomPassword;
			}else{
				$randomPassword = $userPsk;
				$randomPSK = "psk=".$randomPassword;
			}
		}else{
			$randomPassword = $endpointGroupAuthorization['ciscoAVPairPSK'];
			$randomPSK = "psk=".$randomPassword;
		}
		
		if($endpointGroupAuthorization['termLengthSeconds'] == 0){
			$duration = $endpointGroupAuthorization['termLengthSeconds'];
		}else{
			$duration = time() + $endpointGroupAuthorization['termLengthSeconds'];
		}
		
		$wirelessNetwork = $ipskISEDB->getWirelessNetworkById($sanitizedInput['wirelessSSID']);
		
		if($wirelessNetwork){
			$wifiSsid = $wirelessNetwork['ssidName'];
		}
		
		$endpointId = $ipskISEDB->addEndpoint($sanitizedInput['macAddress'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $randomPSK, $duration, $_SESSION['logonSID']);
		
		if($endpointId){
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORCREATE;METHOD:ADD-ENDPOINT;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				
			if($ipskISEDB->addEndpointAssociation($endpointId, $sanitizedInput['macAddress'], $sanitizedInput['associationGroup'], $_SESSION['logonSID'])){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORCREATE;METHOD:ADD-ENDPOINT-ASSOCIATION;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);				
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[unable_to_create_endpoint_association];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:FAILURE[unable_to_create_endpoint];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		}
	}
		
	print $htmlbody;

?>