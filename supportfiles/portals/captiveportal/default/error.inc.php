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
	
	
	//Clear Variables and set to blank
	$pageData['errorMessage'] = "";
    $pageData['createComplete'] = "";
	$pageData['endpointGroupList'] = "";
	$pageData['wirelessSSIDList'] = "";
	$pageData['endpointAssociationList'] = "";


	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];		
		$_SESSION = null;
		session_destroy();
		header("Location: /index.php?portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}");
	}	
	
	$errorId  = (isset($_GET['errorId'])) ? $_GET['errorId'] : '';
	
	$id = filter_var($errorId,FILTER_VALIDATE_INT);
	
 	if($id == 1){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E1}[exceeded_device_count];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "You have exceeded the amount of devices you're allowed to provision.  Please contact the system administrator.";
		$_SESSION['logoutTimer'] = time() + 5;
	}elseif($id == 2){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E2}[no_create_priv];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "You do not have access to provision any devices at this time. Please contact the system administrator.";
		$_SESSION['logoutTimer'] = time() + 5;
	}elseif($id == 3){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E3}[unable_to_id_device];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "The system was unable to identify your device, please try again or contact the system administrator.";
		$_SESSION['logoutTimer'] = time() + 5;
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E4}[internal_server_error];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";AUTHDIRECTORY:".$sanitizedInput['authDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "An internal system error has occured.";
		$_SESSION['logoutTimer'] = time() + 5;
	}

	$homeUrl = "/index.php?portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}";

	print <<< HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.png">
	
	<title>{$portalSettings['portalName']}</title>
    

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="styles/sponsor.css" rel="stylesheet">
  </head>

  <body>
	<div class="container">
		<div class="float-rounded mx-auto shadow-lg p-2 bg-white text-center">
			<div class="mt-2 mb-4">
				<img src="images/iPSK-Logo.svg" width="108" height="57" />
			</div>
			<h1 class="h3 mt-2 mb-4 font-weight-normal">{$portalSettings['portalName']}</h1>
			<div class="alert alert-danger shadow" role="alert"><h6>{$pageData['errorMessage']}</h6></div>
			<a class="btn btn-primary shadow" href="$homeUrl" type="button">Login Page</a>
		</div>
		<div class="m-0 mx-auto p-2 bg-white text-center">
			<p>Copyright &copy; 2019 Cisco and/or its affiliates.</p>
		</div>
		
	</div>

  </body>
  
</html>


HTML;

?>