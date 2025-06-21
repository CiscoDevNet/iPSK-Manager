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
	$pageData['errorMessage'] = "";
    $pageData['createComplete'] = "";
	$pageData['endpointGroupList'] = "";
	$pageData['wirelessSSIDList'] = "";
	$pageData['endpointAssociationList'] = "";


	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];		
		$_SESSION = null;
		session_destroy();
		header("Location: index.php?portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}");
		die();
	}	
	
	$errorId  = (isset($_GET['errorId'])) ? $_GET['errorId'] : '';
	
	$id = filter_var($errorId,FILTER_VALIDATE_INT);
	
 	if($id == 1){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E1}[exceeded_device_count];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";AUTHDIRECTORY:".$_SESSION['portalSettings']['authenticationDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "You have exceeded the amount of devices you're allowed to provision.  Please contact the system administrator.";
		$_SESSION['logoutTimer'] = time() + 5;
	}elseif($id == 2){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E2}[no_create_priv];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";AUTHDIRECTORY:".$_SESSION['portalSettings']['authenticationDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "You do not have access to provision any devices at this time. Please contact the system administrator.";
		$_SESSION['logoutTimer'] = time() + 5;
	}elseif($id == 3){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E3}[unable_to_id_device];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";AUTHDIRECTORY:".$_SESSION['portalSettings']['authenticationDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "The system was unable to identify your device, please try again or contact the system administrator.";
		$_SESSION['logoutTimer'] = time() + 5;
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData();
		$logMessage = "REQUEST:FAILURE{E4}[internal_server_error];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";AUTHDIRECTORY:".$_SESSION['portalSettings']['authenticationDirectory'].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		$pageData['errorMessage'] = "An internal system error has occured.";
		$_SESSION['logoutTimer'] = time() + 5;
	}

	$homeUrl = "/index.php?portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}";

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.png">
	
	<title><?php echo $portalSettings['portalName']?></title>
    

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="styles/sponsor.css" rel="stylesheet">
  </head>
  <body class="text-center">
	<div class="card mx-auto error-page">
		<div class="card-header bg-primary mb-4">
  			<img src="images/ipsk-logo.gif" width="180" height="32" />
		</div>
		<div class="card-body">
			<h1 class="h4 mt-0 mb-4 fw-normal"><?php echo $portalSettings['portalName'];?></h1>
			<div class="alert alert-danger shadow mb-5 h6" role="alert"><?php echo $pageData['errorMessage']?></div>
			
			<a class="btn btn-primary shadow mb-3" href="<?php echo $homeUrl?>" type="button">Login Page</a>

		</div>
		<div class="card-footer bg-light">
			Copyright &copy; 2025 Cisco and/or its affiliates.
		</div>
	</div>
  </body>
</html>