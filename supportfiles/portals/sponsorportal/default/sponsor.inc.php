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
	$pageData['endpointGroupList'] = "";
	$pageData['wirelessSSIDList'] = "";
	$pageData['endpointAssociationList'] = "";
	$pageValid = false;
	
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];		
		$_SESSION = null;
		session_destroy();
		header("Location: index.php?portalId=".$portalId);
		die();
	}
	
	$endpointGroupSelect = (isset($_GET['eg'])) ? $_GET['eg'] : false;
	
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
	
	if(is_array($_SESSION['authorizedEPGroups'])){
		$pageData['endpointGroupList'] .= '<select name="associationGroup" id="associationGroup" class="form-select mb-2 shadow">';
				
		for($count = 0; $count < $_SESSION['authorizedEPGroups']['count']; $count++) {
			if(!isset($trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']])){
				//Check if User is authorized for Create on EndPoint Group
				if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 512){
					$userEPCount = $ipskISEDB->getUserEndpointCount($_SESSION['authorizedEPGroups'][$count]['endpointGroupId'], $_SESSION['logonSID']);	
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

					$disableOption = '';
					if ($userEPCount >= $_SESSION['authorizedEPGroups'][$count]['maxDevices'] && $_SESSION['authorizedEPGroups'][$count]['maxDevices'] != 0) {
						$disableOption = 'disabled'; 
					}
					
					if(!$endpointGroupSelect){
						$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\" $disableOption>".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
					}else{
						if($endpointGroupSelect == $_SESSION['authorizedEPGroups'][$count]['endpointGroupId']){
							$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\" selected $disableOption>".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
						}else{
							$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\" $disableOption>".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
						}
					}
					
					$trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']] = true;
					$pageValid = true;
				}
			}
		}
		$pageData['endpointGroupList'] .= "</select>";
		unset($trackSeenObjects);
	}
	
	if(is_array($_SESSION['authorizedWirelessNetworks'])){
		$pageData['wirelessSSIDList'] .= '<select name="wirelessSSID" class="form-select shadow">';
	
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
		$pageData['createButton'] = '<li class="nav-item"><a class="nav-item nav-link active" id="createAssoc" data-bs-toggle="tab" href="#" role="tab">Create Associations</a></li>';
//		<div class="col py-1"><button id="createAssoc" class="btn btn-primary shadow" type="button">Create Associations</button></div>';
	}else{
		$pageData['createButton'] = '';
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == true){
		$pageData['bulkButton'] = '<li class="nav-item"><a class="nav-item nav-link" id="bulkAssoc" data-bs-toggle="tab" href="#" role="tab">Bulk Associations</a></li>';
		//'<div class="col py-1"><button id="bulkAssoc" class="btn btn-primary shadow" type="button">Bulk Associations</button></div>';
	}else{
		$pageData['bulkButton'] = '';
	}
	
	if(!$pageValid){
		header("Location: manage.php?portalId=".$portalId."&notice=1");
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
				<form id="associationform" action="create.php?portalId=$portalId" method="post">
				<div class="container">
					<div class="row row-cols-1 row-cols-md-2">
					<!--<div class="card mx-auto w-100 h-100">
						<div class="card-body">-->
						
							<div class="col">
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
							<div class="col">
								<div class="card h-100">
          							<div class="card-header bg-primary text-white">Endpoint Details</div>
									<div class="card-body">
				  						
										<div class="container">
											<div class="row">
												<div class="col-sm">
													<div class="mb-3 input-group-sm">
				
														<label class="form-label" for="macAddress">Endpoint MAC Address:</label>
														<input type="text" class="form-control shadow user-input form-validation" validation-state="required" validation-minimum-length="17" validation-maximum-length="17" value="" id="macAddress" name="macAddress" maxlength="17" placeholder="XX:XX:XX:XX:XX:XX">
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
		window.location.href = "sponsor.php?portalId=$portalId";
	});
	
	$("#bulkAssoc").click(function() {
		window.location.href = "bulk.php?portalId=$portalId";
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