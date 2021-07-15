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
	
	//Clear Variables and set to blank
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: /index.php?portalId=".$portalId);
		die();
	}

	$sampleFile = (isset($_GET['samplefile'])) ? (filter_var($_GET['samplefile'],FILTER_VALIDATE_BOOLEAN)) : false;

	$sampleCSV = "macaddress,fullname,emailaddress,description\r\n00:00:00:FF:FF:FF,Sample Name,Sample@Demo.Local,My Device - Mobile Phone";

	if(isset($sanitizedInput['action'])){
		if($sanitizedInput['action'] == "get_endpoint_groups"){
			if($iseERSIntegrationAvailable){
				$endpointIdentityGroups = $ipskISEERS->getEndPointIdentityGroups();

				if($endpointIdentityGroups){
					$endpointIdentityGroupsArray = json_decode($endpointIdentityGroups,TRUE);
					$endpointIdentityGroupsArray = arraySortAlpha($endpointIdentityGroupsArray);
					$endpointIdentityGroups = json_encode($endpointIdentityGroupsArray);

					print $endpointIdentityGroups;
				}
			}
		}elseif($sanitizedInput['action'] == "get_endpoint_count"){
			if($iseERSIntegrationAvailable){
				print $ipskISEERS->getEndPointGroupCountbyId($sanitizedInput['groupUuid']);
			}
		}elseif($sanitizedInput['action'] == "get_random_psk"){
			$authZ = $ipskISEDB->getEndPointAuthorizationPolicy($sanitizedInput['id']);
			
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:SUCCESS;GET-DATA-COMMAND:".$sanitizedInput['action'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			$psk = $ipskISEDB->generateRandomPassword($authZ['pskLength']);
			
			$_SESSION['temp']['expires'] = time() + 600;
			$_SESSION['temp']['sponsoreditpsk'] = password_hash($psk, PASSWORD_DEFAULT);
			
			print $psk;
		}
	}else{
		if($sampleFile == true){
			header('Content-Description: File Transfer');
			header('Content-Type: plain/text');
			header('Content-Disposition: attachment; filename=import_sample.csv'); 
			header('Content-Transfer-Encoding: text');
			header('Content-Length: '.strlen($sampleCSV));
			echo $sampleCSV;
		}
	}
?>