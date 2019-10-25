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
		header("Location: /index.php?portalId=".$portalId);
		die();
	}

	$listCount = 0;
			
	$endpointAssociationList = $ipskISEDB->getEndPointAssociationList($_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id'], $_SESSION['portalAuthorization']['viewall'], $_SESSION['portalAuthorization']['viewallDn']);

	if($endpointAssociationList){
		$pageData['endpointAssociationList'] .= '<table class="table table-hover"><thead><tr><th scope="col">MAC Address</th><th scope="col">Endpoint Group</th><th scope="col">Expiration Date</th><th scope="col">View</th><th scope="col">Actions</th></tr></thead><tbody>';
		
		for($idxId = 0; $idxId < $endpointAssociationList['count']; $idxId++) {
			$viewEnabled = false;
			
			if($_SESSION['portalAuthorization']['viewall'] == true){
				$viewEnabled = true;
			}elseif($endpointAssociationList[$idxId]['viewPermissions'] & 4){
				$viewEnabled = true;
			}elseif($endpointAssociationList[$idxId]['viewPermissions'] & 2){
				for($groupCount = 0; $groupCount < $_SESSION['authorizedEPGroups']['count']; $groupCount++){
					if($endpointAssociationList[$idxId]['epGroupId'] == $_SESSION['authorizedEPGroups'][$groupCount]['endpointGroupId']){
						if($_SESSION['authorizedEPGroups'][$groupCount]['viewPermissions'] & 2){
							$viewEnabled = true;
						}
					}
				}
			}elseif($endpointAssociationList[$idxId]['viewPermissions'] & 1){
				if($endpointAssociationList[$idxId]['createdBy'] == $_SESSION['logonSID']){
					$viewEnabled = true;
				}
			}
			
			if($viewEnabled == true){
				
				if($endpointAssociationList[$idxId]['accountEnabled'] == 1){
					if($endpointAssociationList[$idxId]['expirationDate'] == 0){
						$expiration = "Never";
					}elseif($endpointAssociationList[$idxId]['expirationDate'] < time()){
						$expiration = '<span class="text-danger">Expired</span>';
					}else{
						$expiration = date($globalDateOutputFormat,$endpointAssociationList[$idxId]['expirationDate']);
					}
				}else{
					$expiration = "Suspended";
				}
			
			
				$actionRowData = "";
				
				//Suspend Permission
				if($endpointAssociationList[$idxId]['groupPermissions'] & 16){
					$actionRowData .= '<a class="dropdown-item action-tableicons" module="suspend" row-id="'.$endpointAssociationList[$idxId]['id'].'" href="#">Suspend</a>';
				}
				
				//Activate Permission
				if($endpointAssociationList[$idxId]['groupPermissions'] & 32){
					$actionRowData .= '<a class="dropdown-item action-tableicons" module="activate" row-id="'.$endpointAssociationList[$idxId]['id'].'" href="#">Activate</a>';
				}
				
				//Extend Permission
				if($endpointAssociationList[$idxId]['groupPermissions'] & 128){
					$actionRowData .= '<a class="dropdown-item action-tableicons" module="extend" row-id="'.$endpointAssociationList[$idxId]['id'].'" href="#">Extend</a>';
				}
				
				//Edit Permission
				if($endpointAssociationList[$idxId]['groupPermissions'] & 256){
					$actionRowData .= '<a class="dropdown-item action-tableicons" module="edit" row-id="'.$endpointAssociationList[$idxId]['id'].'" href="#">Edit</a>';
				}	
				
				//Delete Permission
				if($endpointAssociationList[$idxId]['groupPermissions'] & 64){
					$actionRowData .= '<a class="dropdown-item action-tableicons" module="delete" row-id="'.$endpointAssociationList[$idxId]['id'].'" href="#">Delete</a>';
				}
				
				if($actionRowData != ""){
					$actionRow = '<div class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><span data-feather="more-vertical"></span></a><div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'.$actionRowData.'</div></div>';
				}else{
					$actionRow = '<div></div>';
				}			
				
				$associationList[$listCount]['view'] = '<a class="action-tableicons" module="view" row-id="'.$endpointAssociationList[$idxId]['id'].'" href="#"><span data-feather="zoom-in"></span></a>';
				$associationList[$listCount]['action'] = $actionRow;
				$associationList[$listCount]['macAddress'] = $endpointAssociationList[$idxId]['macAddress'];
				$associationList[$listCount]['epGroupName'] = $endpointAssociationList[$idxId]['groupName'];
				$associationList[$listCount]['expiration'] = $expiration;
				$associationList[$listCount]['id'] = $endpointAssociationList[$idxId]['id'];

				$listCount++;
			}
			
		}
	}
	
	$associationList['count'] = $listCount;

	for($assocId = 0;$assocId < $associationList['count']; $assocId++){
		$pageData['endpointAssociationList'] .= '<tr>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['macAddress'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['epGroupName'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['expiration'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['view'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['action'].'</td>';
		$pageData['endpointAssociationList'] .= '</tr>';
	}
	
	$pageData['endpointAssociationList'] .= "</tbody></table>";
		
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
					<div class="col"></div>
					<div class="col-10 mt-2 shadow mx-auto p-2 bg-white border border-primary text-center">
						<h4 class="h4">Manage Endpoint Associations</h4>
					</div>
					<div class="col"></div>
				</div>
				<div class="row text-left">
					<div class="col"></div>
					<div class="col-10 mt-2 shadow mx-auto p-2 bg-white border border-primary">
						{$pageData['endpointAssociationList']}		
					</div>
					<div class="col"></div>
				</div>
		</div>
		<div class="m-0 mx-auto p-2 bg-white text-center">
			<p>Copyright &copy; 2019 Cisco and/or its affiliates.</p>
		</div>
		
	</div>
  <div id="popupcontent"></div>
  </body>
  <script type="text/javascript" src="scripts/jquery-3.3.1.min.js"></script>
  <script type="text/javascript" src="scripts/feather.min.js"></script>
  <script type="text/javascript" src="scripts/popper.min.js"></script>
  <script type="text/javascript" src="scripts/bootstrap.min.js"></script>
  <script type="text/javascript" src="scripts/ipsk-portal-v1.js"></script>
    <script type="text/javascript">
	
	$(function() {	
		feather.replace()
	});
	
	$(".action-tableicons").click(function(event) {
		$.ajax({
			url: "/" + $(this).attr('module') + ".php?portalId=$portalId",
			
			data: {
				id: $(this).attr('row-id')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
				//alert("success");
			}
		});
		
		event.preventDefault();
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
	
	</script>
</html>
HTML;

?>