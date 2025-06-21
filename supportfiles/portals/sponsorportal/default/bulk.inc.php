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
	$pageData['iseEndpointGroups'] = "";
	$pageValid = false;
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];		
		$_SESSION = null;
		session_destroy();
		header("Location: index.php?portalId=".$portalId);
		die();
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == false){
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
				//Check if User is authorized for Bulk Create on EndPoint Group
				if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 2048){
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

					
					$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\" $disableOption>".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
					$trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']] = true;
					$pageValid = true;
				}
			}
		}
		$pageData['endpointGroupList'] .= "</select>";
		unset($trackSeenObjects);
	}
	
	if(is_array($_SESSION['authorizedWirelessNetworks'])){
		$pageData['wirelessSSIDList'] .= '<select name="wirelessSSID" class="form-select mb-3 shadow">';
	
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
		$pageData['createButton'] = '<li class="nav-item"><a class="nav-item nav-link" id="createAssoc" data-bs-toggle="tab" href="#" role="tab">Create Associations</a></li>';
	}else{
		$pageData['createButton'] = '';
	}
	
	if($_SESSION['portalAuthorization']['bulkcreate'] == true){
		$pageData['bulkButton'] = '<li class="nav-item"><a class="nav-item nav-link active" id="bulkAssoc" data-bs-toggle="tab" href="#" role="tab">Bulk Associations</a></li>';
	}else{
		$pageData['bulkButton'] = '';
	}

	if($iseERSIntegrationAvailable){
		$pageData['bulkOption'] = '<option value="1">CSV File Import</option><option value="3">Cisco ISE "Endpoint Group" Import</option>';
	}else{
		$pageData['bulkOption'] = '<option value="1">CSV File Import</option>';
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
  <form id="bulkAssociationform" action="bulkimport.php?portalId=$portalId" method="post">
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
					<div class="row row-cols-1 row-cols-md-2">	
							<div class="col">
								<div class="card h-100">
          							<div class="card-header bg-primary text-white">Access Details</div>
          							<div class="card-body input-group-sm">
										<label class="form-label" for="associationGroup">Access type:</label>	
				  						{$pageData['endpointGroupList']}
										<div class="container-fluid">
											<div class="row">
												<div class="col-md">
													<small>Maximum access duration:&nbsp;<span id="duration" class="text-danger count">-</span></small>
												</div>
												<div class="col-md mb-3">
													<small>Pre Shared Key Type:&nbsp;<span id="keyType" class="text-danger count">-</span></small>
												</div>
											</div>
										</div>
										<label class="form-label" for="wirelessSSID">Wireless SSID:</label>
										{$pageData['wirelessSSIDList']}
										<label class="form-label" for="bulkImportType">Bulk Import Type:</label>
										<select name="bulkImportType" id="bulkImportType" class="form-select mb-3 shadow">
											<option value="0">(Select an Import Option)</option>
											{$pageData['bulkOption']}
										</select>		
										<div id="sampleFileDownload" class="row d-none">
											<div class="col-md">
												CSV Format Sample File Download: <a href="/query.php?portalId=$portalId&samplefile=1">import_sample.csv</a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col">
								<div id="csvBulkImport" class="d-none card h-100">
          							<div class="card-header bg-primary text-white">CSV Import Data</div>
									<div class="card-body">	
										<div class="row">
											<label class="form-label" for="csvFile">Choose CSV File:</label>
											<div class="mb-3 input-group-sm">
									  			<input type="file" accept=".csv" class="form-control user-input shadow" name="csvFile" id="csvFile">
											</div>
											<input type="hidden" name="uploadkey" id="uploadkey" value="">
										</div>
										<div class="row">
											<div class="col d-flex justify-content-center">
												<button class="btn mb-3 btn-primary shadow" id="uploadCsv" type="button" disabled>Upload</button>
											</div>
										</div>
										<div class="row mb-3">
											<div class="text-center">CSV File Upload Details: <span id="uploadMessage" class="text-success d-none"></span></div>
										</div>
										<div class="row">
											<div class="col">
												Total Entries in Upload: <span id="importCount" class="text-success count">-</span>
											</div>
											<div class="col">
												Total Entries to be Imported: <span id="validCount" class="text-success count">-</span>
											</div>
										</div>
										<div class="row">
											<div class="col">
												Entries With Illegal Characters: <span id="invalidCharacters" class="text-danger count">-</span>
											</div>
											<div class="col">
												Total Filtered Entries: <span id="filteredItems" class="text-danger count">-</span>
											</div>
										</div>
										<div class="row">
											<div class="col">
												Entries With Invalid Format: <span id="invalidItems" class="text-danger count">-</span>
											</div>
										</div>
										<div class="row">
											<div class="mb-3 text-center">
												<button class="btn btn-primary shadow" id="submitbtn" type="button">Import</button>
											</div>
										</div>
									</div>
								</div>
								<div id="iseBulkImport" class="d-none card h-100">
									<div class="card-header bg-primary text-white">ISE Endpoint Group Import</div>
									<div class="card-body input-group-sm">	
										<label class="form-label" for="groupUuid">Select the Endpoint Identity Group you would like to import:</label>
										<select name="groupUuid" id="iseEPGroups" class="form-select mb-2 shadow"></select>
										<div class="row">
											<div class="col">
												<div class="row">
													<small>Description:&nbsp;<span id="iseepgDescription" class="text-danger count">-</span></small>
												</div>
												<div class="row mb-3">
													<small>Endpoint Count:&nbsp;<span id="iseepgCount" class="text-danger count">-</span></small>
												</div>
											</div>
											<div class="col d-flex align-items-center">
												<button class="btn btn-sm btn-primary shadow" id="getCount" type="button">Get Count</button>
											</div>
										</div>
										<div class="row mt-3 associationrow">
											<div class="col-sm">
												<div class="mb-3 input-group-sm">
													<label class="form-label" for="endpointDescription">Endpoint Description:</label>
													<input type="text" class="form-control shadow user-input" value="" name="endpointDescription" id="endpointDescription" placeholder="Device Description">
												</div>
											</div>								
											<div class="row associationrow">
												<div class="col-sm">
													<div class="mb-3 input-group-sm">
														<label class="form-label" for="fullName">Full Name:</label>
														<input type="text" class="form-control user-input shadow form-validation" validation-state="required" value="" name="fullName" id="fullName" placeholder="John Smith">
														<div class="invalid-feedback">Please enter your Full Name</div>
													</div>
												</div>
												<div class="col-sm">
													<div class="mb-3 input-group-sm">
														<label class="form-label" for="emailAddress">Email address</label>
														<input type="email" class="form-control user-input shadow form-validation" validation-state="required" value="{$sessionData['emailAddress']}" name="emailAddress" placeholder="john@company.com">
														<div class="invalid-feedback">Please enter a valid email address</div>
													</div>
												</div>
											</div>
											<div class="row associationrow">
												<div class="mb-3 text-center">
													<button class="btn btn-primary shadow" id="iseSubmitbtn" type="button">Import</button>
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
	</form>
	</div>	















										








	

  </body>
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
  <script type="text/javascript" src="scripts/ipsk-portal-v1.js"></script>
    <script type="text/javascript">
	
	var failure;
	
	$("#submitbtn").click(function() {
		event.preventDefault();
		$(this).attr("disabled", true);
		
		if($("#uploadkey").val() == ""){
			failure = formFieldValidation();
		
			if(failure){
				$(this).removeAttr('disabled');
				return false;
			}
		}
		
		$("#bulkAssociationform").submit();
	});

	$("#iseSubmitbtn").click(function() {
		event.preventDefault();
		$(this).attr("disabled", true);
		
		if($("#uploadkey").val() == ""){
			failure = formFieldValidation();
		
			if(failure){
				$(this).removeAttr('disabled');
				return false;
			}
		}
		
		$("#bulkAssociationform").submit();
	});
	
	$("#csvFile").change(function () {
		$("#uploadCsv").removeAttr('disabled');
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
	
	$("#uploadCsv").click(function(event) {		
		event.preventDefault();
		
		$("#associationDetails").addClass('d-none');
		$("#importCount").html( "-" );
		$("#validCount").html( "-" );
		$("#invalidItems").html( "-" );
		$("#filteredItems").html( "-" );
		$("#invalidCharacters").html( "-" );
		$("#uploadkey").val( '' );
		
		var form = $('#bulkAssociationform')[0];
		var data = new FormData(form);
		$.ajax({
			url: "fileupload.php?portalId=$portalId",
			
			data: data,
			type: "POST",
			enctype: 'multipart/form-data',
			processData: false,
			contentType: false,
			cache: false,
			dataType: "json",
			success: function (data) {
				if(data.result){
					$("#importCount").html( data.recordsprocessed );
					$("#validCount").html( data.validitems );
					$("#invalidItems").html( data.invaliditems );
					$("#filteredItems").html( data.filtereditems );
					$("#invalidCharacters").html( data.invalidchar );
					$("#uploadkey").val( data.uploadkey );
					$("#uploadCsv").attr("disabled", true);
					$("#associationDetails").removeClass('d-none');
					$(".associationrow").addClass('d-none');
					$("#uploadMessage").html( data.message );
					$("#uploadMessage").addClass('text-success');
					$("#uploadMessage").removeClass('text-danger');
					$("#uploadMessage").removeClass('d-none');
				}else{
					$("#importCount").html( data.recordsprocessed );
					$("#validCount").html( data.validitems );
					$("#invalidItems").html( data.invaliditems );
					$("#filteredItems").html( data.filtereditems );
					$("#invalidCharacters").html( data.invalidchar );
					$("#uploadkey").val( '' );
					$("#uploadMessage").html( data.message );
					$("#uploadMessage").removeClass('d-none');
					$("#uploadMessage").removeClass('text-success');
					$("#uploadMessage").addClass('text-danger');
					
				}
			}
		});
	});
	
	$("#bulkImportType").change(function(){
		event.preventDefault();
		
		$("#iseBulkImport").addClass('d-none');
		$("#csvBulkImport").addClass('d-none');
		$("#associationDetails").addClass('d-none');
		$("#sampleFileDownload").addClass('d-none');
		$(".associationrow").removeClass('d-none');
		$("#uploadkey").val( '' );
		$( "#iseepgDescription" ).html( "-" );
		$( "#iseepgCount" ).html( "-" );
		
		if($(this).find('option:selected').val() == 1){
			
			$("#csvBulkImport").removeClass('d-none');
			$("#sampleFileDownload").removeClass('d-none');
			
		}else if($(this).find('option:selected').val() == 3){
		
			$.ajax({
				url: "query.php?portalId=$portalId",
				data: {
					'id': $(this).find('option:selected').val(),
					'action': 'get_endpoint_groups'
				},
				type: "POST",
				dataType: "json",
				success: function (epglist) {
					$("#iseEPGroups").find("option").remove(),
					$.each(epglist.SearchResult.resources, function(index, element) {
						temp = $('<option>', {value: element.id, description: element.description}),
						$("#iseEPGroups").append(temp.html(element.name));
					}),
					$("#iseBulkImport").removeClass('d-none');
					$("#associationDetails").removeClass('d-none');
					$("#iseEPGroups").trigger("change");
				}
			});
			
		}
	});
		
	$("#getCount").click(function(){
		event.preventDefault();

		$.ajax({
			url: "query.php?portalId=$portalId",
			data: {
				'groupUuid': $("#iseEPGroups").find('option:selected').val(),
				'action': 'get_endpoint_count'
			},
			type: "POST",
			dataType: "text",
			success: function (epCount) {
				$( "#iseepgCount" ).html( epCount );
			}
		});
	});	
	
	$("#iseEPGroups").change(function() {
		var description = "";
		$( "#iseEPGroups option:selected" ).each(function() {
			name = $(this).html();
			description = $(this).attr("description");
			$( "#iseepgDescription" ).html( description );
			$( "#endpointDescription" ).val( "[IMPORTED] ISE Endpoint Group: " + name );
			$( "#fullName" ).val( "[ISE-IMPORTED] Endpoint" );
			$( "#iseepgCount" ).html( "-" );
		});
	});
		
	$("#associationGroup").change(function() {
		var duration = "";
		var keyType = "";
		$( "#associationGroup option:selected" ).each(function() {
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