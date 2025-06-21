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
	$pageData['endpointAssociationList'] = "";
	$pageData['hidePskFlag'] = "";
	$randomPassword = "";
	$wifiSsid = '';
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: index.php?portalId=".$portalId);
		die();
	}
	
	if($_SESSION['portalAuthorization']['create'] == false){
		header("Location: manage.php?portalId=".$portalId);
		die();
	}

	$adminSettings = $ipskISEDB->getGlobalClassSetting("admin-portal");
	if(isset($adminSettings['use-portal-description'])){
		if($adminSettings['use-portal-description'] == 1) {
			$pageDescription = $portalSettings['description'];
		}
		else {
			$pageDescription = "Manage Identity Pre-Shared Keys (\"iPSK\") Associations";
		}
	}
	else {
		$pageDescription = "Manage Identity Pre-Shared Keys (\"iPSK\") Associations";
	}
	
	$userEPCount = $ipskISEDB->getUserEndpointCount($sanitizedInput['associationGroup'], $_SESSION['logonSID']);
	
	for($count = 0; $count < $_SESSION['authorizedEPGroups']['count']; $count++) {
		if($_SESSION['authorizedEPGroups'][$count]['endpointGroupId'] == $sanitizedInput['associationGroup']){
			$epGroupMax = $_SESSION['authorizedEPGroups'][$count]['maxDevices'];
		}
	}
	
	if($userEPCount < $epGroupMax || $epGroupMax == 0){
		
		$smtpSettings = $ipskISEDB->getSmtpSettings();
		
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
			
			$endpointId = $ipskISEDB->addEndpoint($sanitizedInput['macAddress'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $randomPSK, $endpointGroupAuthorization['vlan'], $endpointGroupAuthorization['dacl'], $duration, $_SESSION['logonSID']);
			
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
					
					if($ipskISEDB->emailEndpointGroup($sanitizedInput['associationGroup'])){
						if($smtpSettings['enabled'] == 1) {
							// Send email through SMTP server
							sendSMTPEmail($sanitizedInput['emailAddress'], $portalSettings['portalName'], $randomPassword, $wifiSsid, $sanitizedInput['macAddress'], $endpointGroupAuthorization['groupName'], $sanitizedInput['endpointDescription'], $sanitizedInput['fullName'], $_SESSION['fullName'], $smtpSettings, $ipskISEDB);
						}
						else {
							// Send email through system mail process
							sendHTMLEmail($sanitizedInput['emailAddress'], $portalSettings['portalName'], $randomPassword, $wifiSsid, $sanitizedInput['macAddress'], $endpointGroupAuthorization['groupName'], $sanitizedInput['endpointDescription'], $sanitizedInput['fullName'], $_SESSION['fullName'], $smtpSettings, $ipskISEDB);
							/*
						 	*Second Method to Send Email.  (Plain Text)
						 	*
						 	*sendEmail($sanitizedInput['emailAddress'],"iPSK Wi-Fi Credentials","You have been successfully setup to connect to the Wi-Fi Network, please use the following Passcode:".$randomPassword."\n\nThank you!",$smtpSettings);
						 	*/
						}
					}
					$pageData['createComplete'] .= "<div class=\"text-success fs-5\">The Endpoint Association has successfully completed.</div><div class=\"mb-3\">The uniquely generated Pre-Shared Key for the end point is:</div>";
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE[unable_to_create_endpoint_association];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					$pageData['createComplete'] .= "<div class=\"text-danger fs-5\">Endpoint Association failed: Unable to create association.</div><div class=\"mb-3\">Check for duplicate enpoints or data input error.</div>";
					$randomPassword = "";
					$pageData['hidePskFlag'] = " d-none";
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[unable_to_create_endpoint];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				$pageData['createComplete'] .= "<div class=\"text-danger fs-5\">Endpoint Association failed: Unable to create endpoint.</div><div class=\"mb-3\">Please contact a support technician for assistance.</div>";
				$randomPassword = "";
				$pageData['hidePskFlag'] = " d-none";
			}
		}
	}
	
	if($_SESSION['portalAuthorization']['create'] == true){
		$pageData['createButton'] = '<li class="nav-item"><a class="nav-item nav-link active" id="createAssoc" data-bs-toggle="tab" href="#" role="tab">Create Associations</a></li>';
	}else{
		$pageData['createButton'] = '';
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == true){
		$pageData['bulkButton'] = '<li class="nav-item"><a class="nav-item nav-link" id="bulkAssoc" data-bs-toggle="tab" href="#" role="tab">Bulk Associations</a></li>';
	}else{
		$pageData['bulkButton'] = '';
	}

	print <<< HTML
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
		<div class="card mx-auto">
			<div class="card-header bg-primary">
				<div class="row">	
					<div class="col">
						<img src="images/ipsk-logo.gif" width="180" height="32" />
					</div>
					<div class="col-6">
						<h4 class="text-center card-header bg-primary text-white pb-0 border-bottom-0">{$portalSettings['portalName']}</h4>
						<h6 class="text-center card-header bg-primary text-white pt-0 border-top-0 border-bottom-0 fst-italic">{$pageDescription}</h6>
					</div>
					<div class="col text-end">
						<a id="signOut" class="nav-link text-white" href="#">Sign out</a>		
					</div>
				</div>
			</div>
			<div class="card-header">
				<ul class="nav nav-pills card-header-pills">
					{$pageData['createButton']}
					{$pageData['bulkButton']}
        			<li class="nav-item">
						<a class="nav-item nav-link" id="manageAssoc" data-bs-toggle="tab" href="#" role="tab">Manage Associations</a>
					</li>
        		</ul>
			</div>
			<div class="card-body">
				<div class="card w-50 mx-auto h-100">
          			<div class="card-header bg-primary text-white">Create Status</div>  
          			<div class="card-body input-group-sm">
						{$pageData['createComplete']}
						<div class="{$pageData['hidePskFlag']}">
							<div class="input-group mb-3 shadow copied-popover" data-bs-animation="true" data-bs-container="body" data-bs-trigger="manual" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="Pre Shared Key has been copied!">
								<div class="input-group-prepend">
									<span class="input-group-text fw-bold shadow" id="basic-addon1">Pre-Shared Key</span>
								</div>
								<input type="text" id="presharedKey" class="form-control shadow" process-value="$randomPassword" value="$randomPassword" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true" readonly>
								<div class="input-group-append">
									<span class="input-group-text fw-bold shadow" id="basic-addon1"><a id="copyPassword" href="#" data-clipboard-target="#presharedKey"><span id="passwordfeather" data-feather="copy"></span></a></span>
								</div>
							</div>		
							Click on the copy button to copy the Pre Shared Key to your Clipboard.
						</div>
						<div class="mt-3 text-center">
							<button id="newAssoc" class="btn btn-primary shadow" type="button">Create Another</button>
						</div>
					</div>				
				</div>	
			</div>
			<div class="card-footer text-center">
			Copyright &copy; 2025 Cisco and/or its affiliates.
			</div>
		</div>
	</div>	
  </body>
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/feather.min.js"></script>
  <script type="text/javascript" src="scripts/popper.min.js"></script>
  <script type="text/javascript" src="scripts/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="scripts/clipboard.min.js"></script>
  <script type="text/javascript" src="scripts/ipsk-portal-v1.js"></script>
  <script type="text/javascript">
	
	$(function() {	
		feather.replace()
	});
	
	var clipboard = new ClipboardJS('#copyPassword');

	clipboard.on('success', function(e) {
		$('.copied-popover').popover('show');
		$('#presharedKey').addClass('is-valid');
		notificationTimer = setInterval("clearNotification()", 7000);

		e.clearSelection();
	});
	
	function clearNotification(){
		$('.copied-popover').popover('hide');
		$('#presharedKey').removeClass('is-valid');
		clearInterval(notificationTimer);
	}
	
	$("#submitbtn").click(function() {
		$("#associationform").submit();
	});
	
	$("#createAssoc").click(function() {
		window.location.href = "sponsor.php?portalId=$portalId&eg={$sanitizedInput['associationGroup']}";
	});
	
	$("#bulkAssoc").click(function() {
		window.location.href = "bulk.php?portalId=$portalId";
	});
	
	$("#newAssoc").click(function() {
		window.location.href = "sponsor.php?portalId=$portalId&eg={$sanitizedInput['associationGroup']}";
	});
	
	$("#manageAssoc").click(function() {
		window.location.href = "manage.php?portalId=$portalId";
	});
	
	$("#signOut").click(function(event) {
		$.ajax({
			url: "logoff.php?portalId=$portalId",
			
			data: {
				logoff: true
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				window.location = "index.php?portalId=$portalId";
			}
		});
		
		event.preventDefault();
	});
	</script>
</html>
HTML;

?>