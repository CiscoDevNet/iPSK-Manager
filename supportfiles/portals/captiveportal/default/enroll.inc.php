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
	$authorizedGroup = false;
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
	
	if(isset($sessionData['portalGET']['client_mac']) && $sessionData['portalGET']['client_mac'] != ""){
		$clientMac = str_replace("-",":",$_SESSION['portalGET']['client_mac']);
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE{E3}[unable_to_id_device];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";AUTHDIRECTORY:".$_SESSION['portalSettings']['authenticationDirectory'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: error.php?errorId=3&portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}");
		die();
	}
	
	if($_SESSION['portalAuthorization']['create'] == false){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE{E2}[no_create_priv];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";AUTHDIRECTORY:".$_SESSION['portalSettings']['authenticationDirectory'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: error.php?errorId=2&portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}");
		die();
	}
	
	if(is_array($_SESSION['authorizedEPGroups'])){
		for($count = 0; $count < $_SESSION['authorizedEPGroups']['count']; $count++) {
			if(!isset($trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']])){
				//Check if User is authorized for Create on EndPoint Group
				if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 512){
					if($ipskISEDB->getUserEndpointCount($_SESSION['authorizedEPGroups'][$count]['endpointGroupId'], $_SESSION['logonSID']) < $_SESSION['authorizedEPGroups'][$count]['maxDevices']){
						if($_SESSION['authorizedEPGroups'][$count]['termLengthSeconds'] == 0){
							$termLength = "No Expiry";
						}else{
							$termLength = ($_SESSION['authorizedEPGroups'][$count]['termLengthSeconds'] / 60 / 60 / 24) . " Days";
						}
						
						if($_SESSION['authorizedEPGroups'][$count]['ciscoAVPairPSK'] == "*userrandom*"){
							$keyType = "Randomly Chosen per User";
						}elseif($_SESSION['authorizedEPGroups'][$count]['ciscoAVPairPSK'] == "*devicerandom*"){
							$keyType = "Randomly Chosen per Device";
						}else{
							$keyType = "Common PSK";
						}
						
						$authorizedGroup = true;
						
						$pageData['endpointGroupList'] .= "<input type=\"hidden\" name=\"associationGroup\" id=\"associationGroup\" data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\">".$_SESSION['authorizedEPGroups'][$count]['groupName'];
						$trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']] = true;
					}
				}
			}
		}
		
		unset($trackSeenObjects);
	}
	
	if($authorizedGroup == false){
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE{E1}[exceeded_device_count];ACTION:CAPTIVEPORTAL;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";AUTHDIRECTORY:".$_SESSION['portalSettings']['authenticationDirectory'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		header("Location: error.php?errorId=1&portalId=$portalId&sessionId={$sessionData['portalGET']['sessionId']}&client_mac={$sessionData['portalGET']['client_mac']}&redirect={$sessionData['portalGET']['redirect']}");
		die();
	}
		
	if(is_array($_SESSION['authorizedWirelessNetworks'])){
		for($count = 0; $count < $_SESSION['authorizedWirelessNetworks']['count']; $count++) {
			if(!isset($trackSeenObjects[$_SESSION['authorizedWirelessNetworks'][$count]['wirelessSSIDId']])){
				$pageData['wirelessSSIDList'] .= "<input type=\"hidden\" name=\"wirelessSSID\" id=\"wirelessSSID\" value=\"".$_SESSION['authorizedWirelessNetworks'][$count]['wirelessSSIDId']."\">".$_SESSION['authorizedWirelessNetworks'][$count]['ssidName'];
				$trackSeenObjects[$_SESSION['authorizedWirelessNetworks'][$count]['wirelessSSIDId']] = true;
			}
		}

		unset($trackSeenObjects);
	}

	print <<< HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
				<form id="associationform" action="create.php?portalId=$portalId" method="post">
				<div class="container">
					<div class="row row-cols-1 row-cols-md-2">
						<div class="col mb-3">
							<div class="card h-100">
          						<div class="card-header bg-primary text-white">Access Details</div>
          						<div class="card-body input-group-sm">
									  
									<label class="form-label" for="associationGroup">Access type:</label>	
			  						{$pageData['endpointGroupList']}
									<div class="container-fluid">
										<div class="row">
											<div class="col-md">
												<p><small>Maximum access duration:&nbsp;<span id="duration" class="text-danger count">-</span></small></p>
											</div>
											<div class="col-md">
												<p><small>Pre Shared Key Type:&nbsp;<span id="keyType" class="text-danger count">-</span></small></p>
											</div>
										</div>
									</div>
									<label class="form-label" for="wirelessSSID">Wireless SSID:</label>
									{$pageData['wirelessSSIDList']}
								</div>
							</div>
						</div>
						<div class="col mb-3">
							<div class="card h-100">
          						<div class="card-header bg-primary text-white">Endpoint Details</div>
								<div class="card-body">	
									<div class="container">
										<div class="row">
											<div class="col-sm">
												<div class="mb-3 input-group-sm">
													<label class="form-label" for="macAddress">Endpoint MAC Address:</label>
													<input type="text" class="form-control shadow user-input form-validation" validation-state="required" validation-minimum-length="17" validation-maximum-length="17" value="{$clientMac}" id="macAddress" name="macAddress" maxlength="17" placeholder="XX:XX:XX:XX:XX:XX">
													<div class="invalid-feedback">Please enter a valid MAC Address</div>
												</div>
											</div>
											<div class="col-sm">
												<div class="mb-3 input-group-sm">
													<label class="form-label" for="endpointDescription">Endpoint Description:</label>
													<input type="text" class="form-control user-input shadow" value="" name="endpointDescription" placeholder="Device Description">
												</div>
											</div>
										</div>
									</div>
									<div class="container">
										<div class="row">
											<div class="col-sm">
												<div class="mb-3 input-group-sm">
													<label class="form-label" for="fullName">Full Name:</label>
													<input type="text" class="form-control user-input shadow form-validation" validation-state="required" value="{$sessionData['fullName']}" name="fullName" placeholder="John Smith">
													<div class="invalid-feedback">Please enter your Full Name</div>
												</div>
											</div>
											<div class="col-sm">
												<div class="mb-3 input-group-sm">
													<label class="form-label" for="emailAddress">Email address:</label>
													<input type="email" class="form-control user-input shadow form-validation" validation-state="required" value="{$sessionData['emailAddress']}" name="emailAddress" placeholder="john@company.com">
													<div class="invalid-feedback">Please enter a valid email address</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>	
						</div>
					</div>
					<div class="row row-cols-1 row-cols-md-1 p-4">
						<div class="text-center">
							<button class="btn btn-primary shadow" id="submitbtn" type="button">Submit</button>
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
  <script type="text/javascript" src="scripts/ipsk-portal-v1.js"></script>
    <script type="text/javascript">
	
	var failure;
	
	$("#submitbtn").click(function() {
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		$("#associationform").submit();
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
	
	$("#submitbtn").click(function(event) {
		event.preventDefault();
	});
	
	$("#associationGroup").change(function() {
		var duration = "";
		var keyType = "";
		duration = $(this).attr("data-term");
		keyType = $(this).attr("data-keytype");
		$( "#duration" ).html( duration );
		$( "#keyType" ).html( keyType );
	});
	
	$("#associationGroup").trigger("change");
	</script>
</html>
HTML;
?>