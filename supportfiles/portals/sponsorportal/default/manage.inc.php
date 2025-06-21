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
		header("Location: index.php?portalId=".$portalId);
		die();
	}

	$pageNotice = (isset($_GET['notice'])) ? $_GET['notice'] : 0;		
	$listCount = 0;

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

	$endpointAssociationList = $ipskISEDB->getEndPointAssociationList($_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id'], $_SESSION['portalAuthorization']['viewall'], $_SESSION['portalAuthorization']['viewallDn']);

	if($endpointAssociationList){
		$pageData['endpointAssociationList'] .= '<table id="endpoint-table" class="table table-hover"><thead><tr id="endpoint-table-filter"><th scope="col" data-dt-order="disable"><label class="form-check-label" for="allCheck">MAC Address</label></th><th scope="col" data-dt-order="disable">Endpoint Group</th><th scope="col" data-dt-order="disable">Expiration Date</th><th scope="col" data-dt-order="disable">Full Name</th><th scope="col" data-dt-order="disable">Email</th><th scope="col" data-dt-order="disable">Description</th><th scope="col">View</th><th scope="col">Actions</th></tr><tr id="endpoint-table-header"><th scope="col"><div class="form-check"><input type="checkbox" class="form-check-input" onclick="event.stopPropagation()" base-value="1" value="0" id="allCheck"><label class="form-check-label" for="allCheck">MAC Address</label></div></th><th scope="col">Endpoint Group</th><th scope="col">Expiration Date</th><th scope="col">Full Name</th><th scope="col">Email</th><th scope="col">Description</th><th scope="col">View</th><th scope="col">Actions</th></tr></thead><tbody>';
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
				$associationList[$listCount]['fullName'] = $endpointAssociationList[$idxId]['fullName'];
				$associationList[$listCount]['emailAddress'] = $endpointAssociationList[$idxId]['emailAddress'];
				$associationList[$listCount]['description'] = $endpointAssociationList[$idxId]['description'];
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
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['fullName'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['emailAddress'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['description'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['view'].'</td>';
		$pageData['endpointAssociationList'] .= '<td>'.$associationList[$assocId]['action'].'</td>';
		$pageData['endpointAssociationList'] .= '</tr>';
	}
	
	$pageData['endpointAssociationList'] .= "</tbody></table>";
		
	if($_SESSION['portalAuthorization']['create'] == true){
		$pageData['createButton'] = '<li class="nav-item"><a class="nav-item nav-link" id="createAssoc" data-bs-toggle="tab" href="#" role="tab">Create Associations</a></li>';
	}else{
		$pageData['createButton'] = '';
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == true){
		$pageData['bulkButton'] = '<li class="nav-item"><a class="nav-item nav-link" id="bulkAssoc" data-bs-toggle="tab" href="#" role="tab">Bulk Associations</a></li>';
	}else{
		$pageData['bulkButton'] = '';
	}
	
	if($pageNotice){
		$pageData['pageNotice'] = '<div class="row"><div class="col-1"></div><div class="col fs-6 text-center text-danger">Notice: You have exceeded your allotment of devices you are allowed to provision</div><div class="col-1"></div></div>';
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
    
	<!-- Datatables core CSS -->
	<link href="styles/datatables.min.css" rel="stylesheet">

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
						<a class="nav-item nav-link active" id="manageAssoc" data-bs-toggle="tab" href="#" role="tab">Manage Associations</a>
					</li>
        		</ul>
			</div>
			<div class="card-body">
				<form id="associationform" action="create.php?portalId=$portalId" method="post">
				<div class="container">
					<div class="row row-cols-1 row-cols-md-1">
						<div class="col">
							<div class="card h-100">
								<div class="card-header bg-primary text-white">Manage Endpoint Associations</div>
								<div id="bulkOptions" class="card-header">			
									<div class="btn-toolbar gap-3">
										<button class="btn btn-primary shadow bulkaction-button" module="bulkupdate" sub-module="suspend" type="button" disabled>Suspend</button>
										<button class="btn btn-primary shadow bulkaction-button" module="bulkupdate" sub-module="activate" type="button" disabled>Activate</button>
										<button class="btn btn-primary shadow bulkaction-button" module="bulkupdate" sub-module="delete" type="delete" disabled>Delete</button>
									</div>
								</div>					
								<div class="card-body input-group-sm">
									<div class="row text-start">
										<div class="col mx-auto bg-white">
											{$pageData['pageNotice']}
											{$pageData['endpointAssociationList']}
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>	
			</div>
			<div class="card-footer text-center">
			Copyright &copy; 2025 Cisco and/or its affiliates.
			</div>
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
			url: $(this).attr('module') + ".php?portalId=$portalId",
			
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
		window.location.href = "sponsor.php?portalId=$portalId";
	});
	
	$("#bulkAssoc").click(function() {
		window.location.href = "bulk.php?portalId=$portalId";
	});
	
	$("#manageAssoc").click(function() {
		window.location.href = "manage.php?portalId=$portalId";
		
		// Comment out if you want to clear table state when pressing manage associations 
		
		//var table = $('#endpoint-table').DataTable();
		//table.state.clear();
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
		var table = $('#endpoint-table').DataTable();
		table.state.clear();
		
		event.preventDefault();
	});
	
	//$('.allCheck').on('click', function(e) {
		
	$("#allCheck").change(function(e){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));
			$(".endpointCheckBox").each(function () {
				$(this).attr('value', $(this).attr('base-value'));
				$(this).prop( "checked", true );
			});
			$(".bulkaction-button").removeAttr('disabled');
		//	$("#bulkOptions").removeClass('d-none');
		}else{
			$(this).attr('value', '0');
			$(".endpointCheckBox").each(function () {
				$(this).attr('value', '0');
				$(this).prop( "checked", false );
			});
			$(".bulkaction-button").attr('disabled',true);
			//$("#bulkOptions").addClass('d-none');
		}
	});

	$(".checkbox-update").change(function(){
		stillChecked = false;

		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));
			$(".bulkaction-button").removeAttr('disabled');
			//$("#bulkOptions").removeClass('d-none');
		}else{
			$(this).attr('value', '0');

			$("#allCheck").prop( "checked", false );

			$(".checkbox-update").each(function () {
				if($(this).val() != 0){
					stillChecked = true;
				}
			});

			if(stillChecked){
				$(".bulkaction-button").removeAttr('disabled');
				//$("#bulkOptions").removeClass('d-none');
			}else{
				$(".bulkaction-button").attr('disabled',true);
				//$("#bulkOptions").addClass('d-none');
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
				url: $(this).attr('module') + ".php?portalId=$portalId",

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
		$('#endpoint-table thead #endpoint-table-filter th').each( function () {
        var title = $('#endpoint-table thead #endpoint-table-header th').eq( $(this).index() ).text();
		if (/^(View|Actions)$/.test(title)) {
			$(this).html('&nbsp;');
		} else {
			$(this).html('<input type="text" placeholder="Filter '+title+'" />');
		}
    	} );

		$("input[placeholder]").each(function () {
        	$(this).attr('size', $(this).attr('placeholder').length);
    	});

		$("#endpoint-table").DataTable({
			"columnDefs": [
				{
            		target: 6,
            		orderable: false
        		},
				{
            		target: 7,
            		orderable: false
        		},
    		],
			layout: {
        		bottomStart: {
            		buttons: ['colvis']
        		}
    		},
			"paging": true,
			"stateSave": true,
			"lengthMenu": [ [15, 30, 45, 60, -1], [15, 30, 45, 60, "All"] ],
			"stateLoadParams": function(settings, data) {
  				for (i = 0; i < data.columns["length"]; i++) {
    				var col_search_val = data.columns[i].search.search;
    				if (col_search_val != "") {
      					$("input", $("#endpoint-table thead #endpoint-table-filter th")[i]).val(col_search_val);
    				}
  				}

			},
		});

		var table = $("#endpoint-table").DataTable();

		// Get State
		if (table.state.loaded() != null) {
			tableState = table.state();
			
			// Enable all columns
			table.column(3).visible(true);
			table.column(4).visible(true);
			table.column(5).visible(true);
		}

		$("#endpoint-table thead #endpoint-table-filter input").on( 'keyup change', function () {
		table
            .column( $(this).parent().parent().index()+':visible' )
            .search( this.value )
            .draw();
    	} );

		// Hide columns after keyup change event registered
		if (table.state.loaded() == null) {
			table.column(3).visible(false);
			table.column(4).visible(false);
			table.column(5).visible(false);
		} else {
			if (!tableState.columns[3].visible) {
				table.column(3).visible(false)
			}
			if (!tableState.columns[4].visible) {
				table.column(4).visible(false)
			}
			if (!tableState.columns[5].visible) {
				table.column(5).visible(false)
			}

		}

	} ); 
/*
	$(document).ready(function () {
		// Clear Datatable Filters On Reload Or Page Change
    	$('.nav-link').click(function () {
			var table = $('#endpoint-table').DataTable();
			//table.state.clear();
    	});
		$(window).on('beforeunload', function() {
			var table = $('#endpoint-table').DataTable();
			//table.state.clear();
    	});
    });
	*/

	</script>
</html>
HTML;

?>