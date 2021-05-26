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
	$pageData['endpointGroupList'] = "";
	$pageData['wirelessSSIDList'] = "";
	$pageData['endpointAssociationList'] = "";
	$pageValid = false;
	
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];		
		$_SESSION = null;
		session_destroy();
		header("Location: /index.php?portalId=".$portalId);
		die();
	}
	
	$endpointGroupSelect = (isset($_GET['eg'])) ? $_GET['eg'] : false;
	
	if($_SESSION['portalAuthorization']['create'] == false){
		header("Location: /manage.php?portalId=".$portalId);
		die();
	}
	
	if(is_array($_SESSION['authorizedEPGroups'])){
		$pageData['endpointGroupList'] .= '<select name="associationGroup" id="associationGroup" class="form-control mt-2 mb-3 shadow">';
				
		for($count = 0; $count < $_SESSION['authorizedEPGroups']['count']; $count++) {
			if(!isset($trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']])){
				//Check if User is authorized for Create on EndPoint Group
				if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 512){
					$userEPCount = $ipskISEDB->getUserEndpointCount($_SESSION['authorizedEPGroups'][$count]['endpointGroupId'], $_SESSION['logonSID']);
					if($userEPCount < $_SESSION['authorizedEPGroups'][$count]['maxDevices'] || $_SESSION['authorizedEPGroups'][$count]['maxDevices'] == 0){
				
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
						
						if(!$endpointGroupSelect){
							$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\">".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
						}else{
							if($endpointGroupSelect == $_SESSION['authorizedEPGroups'][$count]['endpointGroupId']){
								$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\" selected>".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
							}else{
								$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\">".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
							}
						}
						
						$trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']] = true;
						$pageValid = true;
					}
				}
			}
		}
		$pageData['endpointGroupList'] .= "</select>";
		unset($trackSeenObjects);
	}
	
	if(is_array($_SESSION['authorizedWirelessNetworks'])){
		$pageData['wirelessSSIDList'] .= '<select name="wirelessSSID" class="form-control mt-2 mb-3 shadow">';
	
		for($count = 0; $count < $_SESSION['authorizedWirelessNetworks']['count']; $count++) {
			if(!isset($trackSeenObjects[$_SESSION['authorizedWirelessNetworks'][$count]['wirelessSSIDId']])){
				$pageData['wirelessSSIDList'] .= "<option value=\"".$_SESSION['authorizedWirelessNetworks'][$count]['wirelessSSIDId']."\">".$_SESSION['authorizedWirelessNetworks'][$count]['ssidName']."</option>";
				$trackSeenObjects[$_SESSION['authorizedWirelessNetworks'][$count]['wirelessSSIDId']] = true;
			}
		}
		$pageData['wirelessSSIDList'] .= "</select>";
		unset($trackSeenObjects);
	}
	
	if($_SESSION['portalAuthorization']['create'] == true){
		$pageData['createButton'] = '<div class="col py-1"><button id="createAssoc" class="btn btn-primary shadow" type="button">Create Associations</button></div>';
	}else{
		$pageData['createButton'] = '';
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == true){
		$pageData['bulkButton'] = '<div class="col py-1"><button id="bulkAssoc" class="btn btn-primary shadow" type="button">Bulk Associations</button></div>';
	}else{
		$pageData['bulkButton'] = '';
	}
	
	if(!$pageValid){
		header("Location: /manage.php?portalId=".$portalId."&notice=1");
		die();
	}
	
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
			<form id="associationform" action="create.php?portalId=$portalId" method="post">
				<div class="mt-2 mb-4">
					<img src="images/iPSK-Logo.svg" width="108" height="57" />
				</div>
				<h1 class="h3 mt-2 mb-4 font-weight-normal">{$portalSettings['portalName']}</h1>
				<h2 class="h6 mt-2 mb-3 font-weight-normal">Manage Identity Pre-Shared Keys ("iPSK") Associations</h2>
				<div class="mb-3 mx-auto shadow p-2 bg-white border border-primary">
					<div class="container">
						<div class="row">
							{$pageData['createButton']}
							{$pageData['bulkButton']}
							<div class="col py-1">
								<button id="manageAssoc" class="btn btn-primary shadow" type="button">Manage Associations</button>
							</div>
							<div class="col py-1">
								<button id="signOut" class="btn btn-primary shadow" type="button">Sign Out</button>
							</div>
						</div>
					</div>
				</div>
				<div class="container-fluid">
					<div class="row text-left">
						<div class="col-sm"></div>
						<div class="col-10 col-sm-10 mt-2 shadow mx-auto p-2 bg-white border border-primary">
									<h6>Association type:</h6>
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
									<h6>Wireless SSID:</h6>
									{$pageData['wirelessSSIDList']}
						</div>
						<div class="col-sm"></div>
					</div>
				</div>
				<div class="container-fluid">
					<div class="row text-left">
						<div class="col-sm"></div>
						<div class="col-10 col-sm-10 mt-2 shadow mx-auto p-2 bg-white border border-primary">
							<h6>Association Details:</h6>
							<div class="container">
								<div class="row">
									<div class="col-sm">
										<div class="form-group">
											<label for="macAddress">Endpoint MAC Address</label>
											<input type="text" class="form-control mt-2 mb-3 shadow user-input form-validation" validation-state="required" validation-minimum-length="17" validation-maximum-length="17" value="" id="macAddress" name="macAddress" maxlength="17" placeholder="XX:XX:XX:XX:XX:XX">
											<div class="invalid-feedback">Please enter a valid MAC Address</div>
										</div>
									</div>
									<div class="col-sm">
										<div class="form-group">
											<label for="endpointDescription">Endpoint Description</label>
											<input type="text" class="form-control mt-2 mb-3 user-input shadow" value="" name="endpointDescription" placeholder="Device Description">
										</div>
									</div>
								</div>
							</div>
							<div class="container">
								<div class="row">
									<div class="col-sm">
										<div class="form-group">
											<label for="fullName">Full Name</label>
											<input type="text" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" value="{$sessionData['fullName']}" name="fullName" placeholder="John Smith">
											<div class="invalid-feedback">Please enter your Full Name</div>
										</div>
									</div>
									<div class="col-sm">
										<div class="form-group">
											<label for="emailAddress">Email address</label>
											<input type="email" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" value="{$sessionData['emailAddress']}" name="emailAddress" placeholder="john@company.com">
											<div class="invalid-feedback">Please enter a valid email address</div>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group text-center">
								<button class="btn btn-primary shadow" id="submitbtn" type="button">Submit</button>
							</div>
						</div>
						<div class="col-sm"></div>
					</div>
				</div>
			</form>
		</div>
		<div class="m-0 mx-auto p-2 bg-white text-center">
			<p>Copyright &copy; 2019 Cisco and/or its affiliates.</p>
		</div>
		
	</div>

  </body>
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/ipsk-portal-v1.js"></script>
  <script type="text/javascript">
	
	var failure;
	var ctrlActive = false;
	
	$("#macAddress").keydown( function( event ) {
		//Load Event data into Variables
		var keyPressed = event.key;
		var charPressed = event.which;

		if(charPressed  == 17 || charPressed  == 19){
			ctrlActive = true;
		}else if(keyPressed.match(/c|x|v|C|V|X/g) && !ctrlActive){
			if(!keyPressed.match(/[a-f]|[A-F]|[0-9]/g)) {
				event.preventDefault();
			}
		}else if(!ctrlActive){
			if(!keyPressed.match(/[a-f]|[A-F]|[0-9]/g)) {
				event.preventDefault();
			}
		}
	});
	
	$("#macAddress").keyup( function( event ) {
		//Load Event data into Variables
		var keyPressed = event.key;
		var charPressed = event.which;
		
		if(charPressed  == 17 || charPressed  == 19){
			ctrlActive = false;
		}
		if(charPressed  != 8){
			macAddressFormat($(this));
		}
	});
	
	$("#macAddress").focusout( function( event ) {
		macAddressFormat($(this));
	});
	
	$("#submitbtn").click(function() {
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		$("#associationform").submit();
	});
	
		$("#createAssoc").click(function() {
		window.location.href = "/sponsor.php?portalId=$portalId";
	});
	
	$("#bulkAssoc").click(function() {
		window.location.href = "/bulk.php?portalId=$portalId";
	});
	
	$("#manageAssoc").click(function() {
		window.location.href = "/manage.php?portalId=$portalId";
	});
	
	$("#signOut").click(function(event) {
		$.ajax({
			url: "/logoff.php?portalId=$portalId",
			
			data: {
				logoff: true
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				window.location = "/index.php?portalId=$portalId";
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
		$( "select option:selected" ).each(function() {
			duration = $(this).attr("data-term");
			keyType = $(this).attr("data-keytype");
			$( "#duration" ).html( duration );
			$( "#keyType" ).html( keyType );
		});
	});
	
	$("#associationGroup").trigger("change");
	</script>
</html>


HTML;

?>