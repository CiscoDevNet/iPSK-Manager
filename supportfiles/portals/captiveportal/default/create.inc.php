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
	
	$pageData['createComplete'] = "";
	$pageData['hidePskFlag'] = "";
	$wifiSsid = '';
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: index.php?portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}");
		die();
	}
	
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
			$randomPSK = "psk=".$userPsk;
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
		
		if($endpointId = $ipskISEDB->addEndpoint($sanitizedInput['macAddress'], $sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $randomPSK, $endpointGroupAuthorization['vlan'], $endpointGroupAuthorization['dacl'], $duration, $_SESSION['logonSID'])){
			
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:SUCCESS;ACTION:CAPTIVECREATE;METHOD:ADD-ENDPOINT;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);	
			
			$smtpSettings = $ipskISEDB->getSmtpSettings();
			
			if($ipskISEDB->addEndpointAssociation($endpointId, $sanitizedInput['macAddress'], $sanitizedInput['associationGroup'], $_SESSION['logonSID'])){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:SUCCESS;ACTION:CAPTIVECREATE;METHOD:ADD-ENDPOINT-ASSOCIATION;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";SID:".$_SESSION['logonSID'].";";
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
				$logMessage = "REQUEST:FAILURE[unable_to_create_endpoint_association];ACTION:CAPTIVECREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				$pageData['createComplete'] .= "<div class=\"text-danger fs-5\">Endpoint Association failed: Unable to create association.</div><div class=\"mb-3\">Check for duplicate enpoints or data input error.</div>";
				$randomPassword = "";
				$pageData['hidePskFlag'] = " d-none";
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:FAILURE[unable_to_create_endpoint];ACTION:CAPTIVECREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$sanitizedInput["inputUsername"].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			$pageData['createComplete'] .= "<div class=\"text-danger fs-5\">Endpoint Association failed: Unable to create endpoint.</div><div class=\"mb-3\">Please contact a support technician for assistance.</div>";
			$randomPassword = "";
			$pageData['hidePskFlag'] = " d-none";
		}
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
					<div class="col">
						<a id="signOut" class="nav-link text-end text-white" href="#"><span class="align-middle">Sign out</span></a>	
					</div>
				</div>
			</div>
			<div class="card-header bg-light">
					<div class="col text-center text-primary mb-0 h5">
						{$portalSettings['portalName']}
					</div>
			</div>
			<div class="card-body">
				<div class="card mx-auto h-100" style="max-width: 540px;">
          			<div class="card-header bg-primary text-white">Create Status</div>  
          			<div class="card-body input-group-sm">
						{$pageData['createComplete']}
						<div class="col{$pageData['hidePskFlag']}">
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
  <script type="text/javascript">
	
	$(function() {	
		feather.replace()
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
				window.location = "index.php?portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}";
			}
		});
		
		event.preventDefault();
	});

	var clipboard = new ClipboardJS('#copyPassword');

	clipboard.on('success', function(e) {
		$('.copied-popover').popover('show');
		$('#presharedKey').addClass('is-valid');
		notificationTimer = setInterval("clearNotification()", 7000);
		coaTimer = setInterval("performCoA()", 5000);
		
		e.clearSelection();
	});
	
	function clearNotification(){
		$('.copied-popover').popover('hide');
		$('#presharedKey').removeClass('is-valid');
		clearInterval(notificationTimer);
		clearInterval(coaTimer);
	}
	
	function performCoA(){
		$.ajax({
			url: "performcoa.php?portalId=$portalId",
			
			data: {
				performcoa: true
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				
			}
		});
	}
	
	</script>
</html>
HTML;

?>