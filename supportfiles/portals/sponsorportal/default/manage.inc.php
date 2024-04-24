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
	$pageData['pageinationOutput'] = '';
	$pageStart = 0;
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: /index.php?portalId=".$portalId);
		die();
	}

	$pageNotice = (isset($_GET['notice'])) ? $_GET['notice'] : 0;		
	$listCount = 0;

	$endpointAssociationList = $ipskISEDB->getEndPointAssociationList($_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id'], $_SESSION['portalAuthorization']['viewall'], $_SESSION['portalAuthorization']['viewallDn']);

	if($endpointAssociationList){
		$pageData['endpointAssociationList'] .= '<table id="endpoint-table" class="table table-hover"><thead><tr><th scope="col"><div class="form-check"><input type="checkbox" class="form-check-input" base-value="1" value="0" id="allCheck"><label class="form-check-label" for="allCheck">MAC Address</label></div></th><th scope="col">Endpoint Group</th><th scope="col">Expiration Date</th><th scope="col">View</th><th scope="col">Actions</th></tr></thead><tbody>';
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
					$actionRow = '<div class="dropdown"><a class="dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><span data-feather="more-vertical"></span></a><div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'.$actionRowData.'</div></div>';
				}else{
					$actionRow = '<div></div>';
				}			
				
				$associationList[$listCount]['view'] = '<a class="action-tableicons" module="view" row-id="'.$endpointAssociationList[$idxId]['id'].'" href="#"><span data-feather="zoom-in"></span></a>';
				$associationList[$listCount]['action'] = $actionRow;
				$associationList[$listCount]['macAddress'] = '<div class="form-check"><input type="checkbox" class="form-check-input checkbox-update endpointCheckBox" name="multiEndpoint" base-value="'.$endpointAssociationList[$idxId]['id'].'" value="0" id="multiEndpoint-'.$endpointAssociationList[$idxId]['id'].'"><label class="form-check-label" for="multiEndpoint-'.$endpointAssociationList[$idxId]['id'].'">'.$endpointAssociationList[$idxId]['macAddress'].'</label></div>';
				$associationList[$listCount]['epGroupName'] = $endpointAssociationList[$idxId]['groupName'];
				$associationList[$listCount]['expiration'] = $expiration;
				$associationList[$listCount]['id'] = $endpointAssociationList[$idxId]['id'];

				$listCount++;
			}
			
		}
	}
	
	for($assocId = $pageStart;$assocId < $listCount; $assocId++){
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
		$pageData['createButton'] = '<div class="col py-1"><button id="createAssoc" class="btn btn-primary shadow" type="button">Create Associations</button></div>';
	}else{
		$pageData['createButton'] = '';
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == true){
		$pageData['bulkButton'] = '<div class="col py-1"><button id="bulkAssoc" class="btn btn-primary shadow" type="button">Bulk Associations</button></div>';
	}else{
		$pageData['bulkButton'] = '';
	}
	
	if($pageNotice){
		$pageData['pageNotice'] = '<div class="row"><div class="col-1"></div><div class="col"><span class="h5 text-danger"><strong>Notice:</strong> You have exceeded your allotment of devices you are allowed to Provision</span></div><div class="col-1"></div></div>';
	}else{
		$pageData['pageNotice'] = "";
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
  <style>
	button.buttons-colvis {
    	background: #0d6efd !important;
	}
  </style>
  <body>
	<div class="container">
		<div class="float-rounded mx-auto shadow-lg p-2 bg-white text-center">
			{$pageData['pageNotice']}
			<div class="mt-2 mb-4">
				<img src="images/iPSK-Logo.svg" width="108" height="57" />
			</div>
			<h1 class="h3 mt-2 mb-4 fw-normal">{$portalSettings['portalName']}</h1>
			<h2 class="h6 mt-2 mb-3 fw-normal">Manage Identity Pre-Shared Keys ("iPSK") Associations</h2>
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
			<div class="row text-start">
				<div class="col-sm"></div>
				<div class="col-10 col-sm-10 mt-2 shadow mx-auto p-2 bg-white border border-primary text-center">
					<h4 class="h4">Manage Endpoint Associations</h4>
				</div>
				<div class="col-sm"></div>
			</div>
			<div id="bulkOptions" class="row text-start d-none">
				<div class="col-sm"></div>
				<div class="col-10 mt-2 shadow mx-auto p-2 bg-white border border-primary text-center">
					<h5 class="h5 text-danger">Bulk Selected Options</h5>
					<div class="row">
						<div class="col"><button class="btn btn-primary shadow bulkaction-button" module="bulkupdate" sub-module="suspend" type="button">Suspend</button></div>
						<div class="col"><button class="btn btn-primary shadow bulkaction-button" module="bulkupdate" sub-module="activate" type="button">Activate</button></div>
						<div class="col"><button class="btn btn-primary shadow bulkaction-button" module="bulkupdate" sub-module="delete" type="delete">Delete</button></div>
					</div>
				</div>
				<div class="col-sm"></div>
			</div>
			<div class="overflow-auto row text-start">
				<div class="col-sm"></div>
				<div class="col-10 mt-2 shadow mx-auto p-2 bg-white border border-primary">
					<div class="table-responsive">
						{$pageData['endpointAssociationList']}
					</div>
					<div class="row">
						<div class="col"><hr></div>
					</div>
				</div>
				<div class="col-sm"></div>
			</div>
		</div>
		<div class="m-0 mx-auto p-2 bg-white text-center">
			<p>Copyright &copy; 2024 Cisco and/or its affiliates.</p>
		</div>
	</div>
  <div id="popupcontent"></div>
  </body>
  <!-- Javascript DataTables -->
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <link href="styles/datatables.min.css" rel="stylesheet">
  <script type="text/javascript" src="scripts/datatables.min.js"></script>
  <script type="text/javascript" src="scripts/feather.min.js"></script>
  <script type="text/javascript" src="scripts/popper.min.js"></script>
  <script type="text/javascript" src="scripts/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="scripts/ipsk-portal-v1.js"></script>
  <script type="text/javascript">
	var formData;
	var stillChecked;
	
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
		
		// Comment out if you want to clear table state when pressing manage associations 
		
		//var table = $('#endpoint-table').DataTable();
		//table.state.clear();
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
		var table = $('#endpoint-table').DataTable();
		table.state.clear();
		
		event.preventDefault();
	});
	
	$("#allCheck").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));
			$(".endpointCheckBox").each(function () {
				$(this).attr('value', $(this).attr('base-value'));
				$(this).prop( "checked", true );
			});
			$("#bulkOptions").removeClass('d-none');
		}else{
			$(this).attr('value', '0');
			$(".endpointCheckBox").each(function () {
				$(this).attr('value', '0');
				$(this).prop( "checked", false );
			});
			$("#bulkOptions").addClass('d-none');
		}
	});

	$(".checkbox-update").change(function(){
		stillChecked = false;

		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));
			$("#bulkOptions").removeClass('d-none');
		}else{
			$(this).attr('value', '0');

			$("#allCheck").prop( "checked", false );

			$(".checkbox-update").each(function () {
				if($(this).val() != 0){
					stillChecked = true;
				}
			});

			if(stillChecked){
				$("#bulkOptions").removeClass('d-none');
			}else{
				$("#bulkOptions").addClass('d-none');
			}
		}
	});

	$(".bulkaction-button").click(function(event) {
		formData = new FormData();
		var multiSelect;

		formData.append('sub-module', $(this).attr('sub-module'));

		$(".endpointCheckBox").each(function() {
			if($(this).val() != 0){
				formData.append('id[]', $(this).val());
				multiSelect = true;
			}
		});

		if(multiSelect){
			$.ajax({
				url: "/" + $(this).attr('module') + ".php?portalId=$portalId",

				data: formData,
				processData: false,
				contentType: false,
				type: "POST",
				dataType: "html",
				success: function (data) {
					$('#popupcontent').html(data);
				}
			});
		}

		event.preventDefault();
	});

	$(document).ready( function makeDataTable() {
		$("#endpoint-table").DataTable({
			layout: {
        		bottomStart: {
            		buttons: ['colvis']
        		}
    		},
			"paging": true,
			"stateSave": true,
			"lengthMenu": [ [15, 30, 45, 60, -1], [15, 30, 45, 60, "All"] ]
		});
	} );

	</script>
</html>
HTML;

?>