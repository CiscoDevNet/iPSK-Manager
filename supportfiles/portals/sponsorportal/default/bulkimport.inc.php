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
	$pageData['hidePskFlag'] = "";
	$randomPassword = "";
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: /index.php?portalId=".$portalId);
		die();
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == false){
		header("Location: /manage.php?portalId=".$portalId);
		die();
	}
		
	if($sanitizedInput['associationGroup'] != 0 && $sanitizedInput['wirelessSSID'] != 0 && $sanitizedInput['bulkImportType'] != 0 && $sanitizedInput['emailAddress'] != "" && $sanitizedInput['fullName'] != "" && $sanitizedInput['groupUuid'] != "") {	
		$endpointGroupAuthorization = $ipskISEDB->getAuthorizationTemplatesbyEPGroupId($sanitizedInput['associationGroup']);
		
		if($endpointGroupAuthorization['ciscoAVPairPSK'] == "*devicerandom*"){
			$randomPassword = generatePsk($endpointGroupAuthorization['pskLength']);
			$randomPSK = "psk=".$randomPassword;
		}elseif($endpointGroupAuthorization['ciscoAVPairPSK'] == "*userrandom*"){
			$userPsk = $ipskISEDB->getUserPreSharedKey($sanitizedInput['associationGroup'],$_SESSION['logonSID']);
			if(!$userPsk){
				$randomPassword = generatePsk($endpointGroupAuthorization['pskLength']);
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
		
		$macaddressArray = json_decode($ipskISEERS->getEndPointsByEPGroup($sanitizedInput['groupUuid']), true);
		
		$count = 0;
		
		if($macaddressArray['SearchResult']['total'] > 0){
			foreach($macaddressArray['SearchResult']['resources'] as $entry){
				$macAddressList[$count] = $entry['name'];
				$count++;
			}
		
			$macAddressInsertID = $ipskISEDB->addBulkEndpoints($macAddressList,$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $randomPSK, $duration, $_SESSION['logonSID']);
			
			if($macAddressInsertID){
				if($macAddressInsertID['processed'] > 0){
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
					$logMessage = "BULKREQUEST:SUCCESS;ACTION:SPONSORCREATE;METHOD:ADD-ENDPOINT;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						
					if($ipskISEDB->addBulkEndpointAssociation($macAddressInsertID, $sanitizedInput['associationGroup'], $_SESSION['logonSID'])){
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
						$logMessage = "BULKREQUEST:SUCCESS;ACTION:SPONSORCREATE;METHOD:ADD-ENDPOINT-ASSOCIATION;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Association has successfully completed.</h3><h6></h6></div></div>";
						
						if(is_array($macAddressInsertID)){
							$insertAssociation = "";
							
							for($rowCount = 0; $rowCount < $macAddressInsertID['count']; $rowCount++){
								
								if($macAddressInsertID[$rowCount]['exists'] == true){
									$insertAssociation .= '<tr><td><div><span style="color: #ff0000" data-feather="x-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td><strong>Endpoint Exists</strong></td></tr>';
								}else{
									$insertAssociation .= '<tr><td><div><span style="color: #2d8c32" data-feather="check-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td>'.str_replace("psk=","", $randomPSK).'</td></tr>';
								}
							}
						}
		  
						$pageData['createComplete'] .= "<table class=\"table table-hover\"><thead><tr><th scope=\"col\">MAC Address</th><th scope=\"col\">Pre-Shared Key</th></tr></thead><tbody>$insertAssociation</tbody></table>";
						$randomPassword = "";
						$pageData['hidePskFlag'] = " d-none";
					}else{
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
						$logMessage = "BULKREQUEST:FAILURE[unable_to_create_endpoint_association];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Import has failed.</h3><br><h5 class=\"text-danger\">(Error message: Unable to create associations for endpoints)</h5></div></div>";
						
						if(is_array($macAddressInsertID)){
							$insertAssociation = "";
							
							for($rowCount = 0; $rowCount < $macAddressInsertID['count']; $rowCount++){
								
								if($macAddressInsertID[$rowCount]['exists'] == true){
									$insertAssociation .= '<tr><td><div><span style="color: #ff0000" data-feather="x-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td><strong>Endpoint Exists</strong></td></tr>';
								}else{
									$insertAssociation .= '<tr><td><div><span style="color: #2d8c32" data-feather="check-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td>'.str_replace("psk=","", $randomPSK).'</td></tr>';
								}
							}
						}
		  
						$pageData['createComplete'] .= "<table class=\"table table-hover\"><thead><tr><th scope=\"col\">MAC Address</th><th scope=\"col\">Pre-Shared Key</th></tr></thead><tbody>$insertAssociation</tbody></table>";
						$randomPassword = "";
						$pageData['hidePskFlag'] = " d-none";
					}
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
					$logMessage = "BULKREQUEST:FAILURE[endpoints_exists];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Import has failed.</h3><h6 class=\"text-danger\">(Error message: Endpoints already exist)</h6></div></div>";
						
					if(is_array($macAddressInsertID)){
						$insertAssociation = "";
						
						for($rowCount = 0; $rowCount < $macAddressInsertID['count']; $rowCount++){
							
							if($macAddressInsertID[$rowCount]['exists'] == true){
								$insertAssociation .= '<tr><td><div><span style="color: #ff0000" data-feather="x-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td><strong>Endpoint Exists</strong></td></tr>';
							}else{
								$insertAssociation .= '<tr><td><div><span style="color: #2d8c32" data-feather="check-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td>'.str_replace("psk=","", $randomPSK).'</td></tr>';
							}
						}
					}
	  
					$pageData['createComplete'] .= "<table class=\"table table-hover\"><thead><tr><th scope=\"col\">MAC Address</th><th scope=\"col\">Pre-Shared Key</th></tr></thead><tbody>$insertAssociation</tbody></table>";
					$randomPassword = "";
					$pageData['hidePskFlag'] = " d-none";
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
				$logMessage = "BULKREQUEST:FAILURE[unable_to_create_endpoint];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Association has failed, please contact a support technician for assistance.</h3><h5 class=\"text-danger\">(Error message: Unable to create endpoint)</h5><hr>";

				$randomPassword = "";
				$pageData['hidePskFlag'] = " d-none";
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macaddressArray"=>$macaddressArray));
			$logMessage = "BULKREQUEST:SUCCESS[no_ise_endpoints_found];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>There were no Endpoints found from the import.</h3><h6 class=\"text-danger\">Please check the source data and confirm it conforms to the format.</h6><h6 class=\"text-danger\">(Error message: No endpoints found during import)</h6></div></div>";
			$randomPassword = "";
			$pageData['hidePskFlag'] = " d-none";
		}
	}
	
	if($_SESSION['portalAuthorization']['create'] == true){
		$pageData['createButton'] = '<button id="createAssoc" class="btn btn-primary shadow" type="button">Create Associations</button>';
	}else{
		$pageData['createButton'] = '';
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == true){
		$pageData['bulkButton'] = '<button id="bulkAssoc" class="btn btn-primary shadow" type="button">Bulk Associations</button>';
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
		<div class="float-rounded mx-auto shadow-lg p-2 bg-white text-center">
				<div class="mt-2 mb-4">
					<img src="images/iPSK-Logo.svg" width="108" height="57" />
				</div>
				<h1 class="h3 mt-2 mb-4 font-weight-normal">{$portalSettings['portalName']}</h1>
				<h2 class="h6 mt-2 mb-3 font-weight-normal">Manage Identity Pre-Shared Keys ("iPSK") Associations</h2>
				<div class="mb-3 mx-auto shadow p-2 bg-white border border-primary">
					<div class="row">
						<div class="col-3">				
						{$pageData['createButton']}
						</div>
						<div class="col-3">				
						{$pageData['bulkButton']}
						</div>
						<div class="col-3">				
							<button id="manageAssoc" class="btn btn-primary shadow" type="button">Manage Associations</button>
						</div>
						<div class="col-3">				
							<button id="signOut" class="btn btn-primary shadow" type="button">Sign Out</button>
						</div>
					</div>
				</div>
				
				<div class="row text-left">
					<div class="col-2"></div>
					<div class="col-8 mb-3 mx-auto shadow p-2 bg-white border border-primary">
						<div class="row m-auto text-left">
							{$pageData['createComplete']}
						</div>
						<div class="row">
							<div class="col{$pageData['hidePskFlag']}">
								<div class="input-group input-group-sm mb-3 shadow copied-popover" data-animation="true" data-container="body" data-trigger="manual" data-toggle="popover" data-placement="top" data-content="Pre Shared Key has been Copied!">
									<div class="input-group-prepend">
										<span class="input-group-text font-weight-bold shadow" id="basic-addon1">Pre-Shared Key</span>
									</div>
									<input type="text" id="presharedKey" class="form-control shadow" process-value="$randomPassword" value="$randomPassword" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true" readonly>
									<div class="input-group-append">
										<span class="input-group-text font-weight-bold shadow" id="basic-addon1"><a id="copyPassword" href="#" data-clipboard-target="#presharedKey"><span id="passwordfeather" data-feather="copy"></span></a></span>
									</div>
								</div>
								Click on the copy button to copy the Pre Shared Key to your Clipboard.
							</div>
						</div>
						<div class="row">
							<div class="col text-center">
								<button id="newbulkAssoc" class="btn btn-primary shadow" type="button">Import Again</button>
							</div>
						</div>
					</div>
					<div class="col-2"></div>
				</div>

			<form action="login.php" method="post" class="form-signin">
			</form>
		</div>
		<div class="m-0 mx-auto p-2 bg-white text-center">
			<p>Copyright &copy; 2019 Cisco and/or its affiliates.</p>
		</div>
		
	</div>

  </body>
  <script type="text/javascript" src="scripts/jquery-3.3.1.min.js"></script>
  <script type="text/javascript" src="scripts/feather.min.js"></script>
  <script type="text/javascript" src="scripts/popper.min.js"></script>
  <script type="text/javascript" src="scripts/bootstrap.min.js"></script>
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
		window.location.href = "/sponsor.php?portalId=$portalId";
	});
	
	$("#bulkAssoc").click(function() {
		window.location.href = "/bulk.php?portalId=$portalId";
	});
	
	$("#newbulkAssoc").click(function() {
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
	</script>
</html>
HTML;

?>