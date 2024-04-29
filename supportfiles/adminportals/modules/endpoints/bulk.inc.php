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
	
	$endpointGroups = $ipskISEDB->getEndpointGroupsAndAuthz();
	$wirelessNetworks = $ipskISEDB->getWirelessNetworks();
	
	if($endpointGroups){
		$pageData['endpointGroupList'] .= '<select name="associationGroup" id="associationGroup" class="form-select shadow form-validation">';
		
		while($row = $endpointGroups->fetch_assoc()) {		
			if($row["visible"] == true){
				if($row['termLengthSeconds'] == 0){
					$termLength = "No Expiry";
				}else{
					$termLength = ($row['termLengthSeconds'] / 60 / 60 / 24) . " Days";
				}

				if($row['ciscoAVPairPSK'] == "*userrandom*"){
					$keyType = "Randomly Chosen per User";
				}elseif($row['ciscoAVPairPSK'] == "*devicerandom*"){
					$keyType = "Randomly Chosen per Device";
				}else{
					$keyType = "Common PSK";
				}
				
				$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$row['id']."\">".$row['groupName']."</option>";
			}
		}
		$pageData['endpointGroupList'] .= "</select>";
	}
	
	if($wirelessNetworks){
		$pageData['wirelessSSIDList'] .= '<select name="wirelessSSID" id="wirelessSSID" class="form-select shadow form-validation">';
	
		while($row = $wirelessNetworks->fetch_assoc()) {		

			$pageData['wirelessSSIDList'] .= "<option value=\"".$row['id']."\">".$row['ssidName']."</option>";			
		}
		
		$pageData['wirelessSSIDList'] .= "</select>";
	}
	
	if($iseERSIntegrationAvailable){
		$pageData['bulkOption'] = '<option value="1">CSV File Import</option><option value="3">Cisco ISE "Endpoint Group" Import</option>';
	}else{
		$pageData['bulkOption'] = '<option value="1">CSV File Import</option>';
	}

	$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="bulkAddEndpointDialog" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Bulk Endpoint Import</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					
				</button>
			</div>
			<div class="modal-body">
				<form id="bulkAssociationform" class="needs-validation" novalidate>
					<div class="row">
						<div class="col mt-2 shadow mx-auto p-2 bg-white border border-primary">
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
					</div>
					<div class="row">
						<div class="col mt-2 shadow mx-auto p-2 bg-white border border-primary">
							<h6>Bulk Import Type:</h6>
							<div class="container-fluid">
								<div class="row">
									<div class="col-md">
										<select name="bulkImportType" id="bulkImportType" class="form-select mt-2 mb-3 shadow"><option value="0">(Select an Import Option)</option>{$pageData['bulkOption']}</select>
									</div>
								</div>
								<div id="sampleFileDownload" class="row d-none">
									<div class="col-md">
										CSV Format Sample File Download: <a href="ajax/getdata.php?samplefile=1">import_sample.csv</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="csvBulkImport" class="d-none row">
						<div class="col mt-2 shadow mx-auto p-2 bg-white border border-primary">
							<h6>Upload CSV File to Import:</h6>
							<div class="row">
								<div class="col">
									<div class="mb-3">
									  <label for="csvFile">Choose CSV File:</label>
									  <input type="file" accept=".csv" class="form-control" name="csvFile" id="csvFile">
									</div>
									<input type="hidden" id="uploadkey" value="">
								</div>
							</div>
							<div class="row mx-auto">
								<div class="col-3"><button class="btn btn-primary shadow" id="uploadCsv" type="button" disabled>Upload</button></div>
								<div class="col"><span id="uploadMessage" class="fw-bold text-success d-none"></span></div>
							</div>
							<div class="row mx-auto">
								<div class="col text-primary fw-bold text-center">CSV File Upload Details</div>
							</div>
							<div class="row mx-auto">
								<div class="col border border-secondary">
									<p><small>
										<span id="" class="h6 text-secondary">Total Entries in Upload:</span><span id="importCount" class="ps-2 h5 text-success count">-</span>
									</small></p>
								</div>
								<div class="col border border-secondary">
									<p><small>
										<span id="" class="h6 text-secondary">Total Entries to be Imported:</span><span id="validCount" class="ps-2 h5 text-success count">-</span>
									</small></p>
								</div>
							</div>
							<div class="row mx-auto">
								<div class="col border border-secondary">
									<h6 class="fw-bold text-center">Total Invalid Entries</h6>
									<div class="row">
										<div class="col border border-secondary">
											<p><small>
												<span class="h6 text-secondary">Illegal Characters:</span><span id="invalidCharacters" class="ps-2 h5 text-danger count">-</span>
											</small></p>
										</div>
										<div class="col border border-secondary">
											<p><small>
												<span class="h6 text-secondary">Entry Format:</span><span id="invalidItems" class="ps-2 h5 text-danger count">-</span>
											</small></p>
										</div>
									</div>
								</div>
								<div class="col border border-secondary">
									<p><small>
										<span class="h6 text-secondary">Total Filtered Entries:</span><span id="filteredItems" class="ps-2 h5 text-danger count">-</span>
									</small></p>
								</div>
							</div>
						</div>
					</div>
					<div id="iseBulkImport" class="d-none row text-start">
						<div class="col mt-2 shadow mx-auto p-2 bg-white border border-primary">
							<h6>Select the Endpoint Identity Group you would like to import:</h6>
							<div class="row">
								<div class="col">
									<div class="mb-3">
										<select name="groupUuid" id="groupUuid" class="form-select mt-2 mb-3 shadow"></select>
									</div>
								</div>
							</div>
							<div class="container-fluid">
								<div class="row">
									<div class="col-md pe-0">
										<p><small>
											Description:&nbsp;<span id="iseepgDescription" class="text-danger count">-</span>
										</small></p>
									</div>
									<div class="col-md-4 ps-0">
										<p><small>
											Endpoint Count:&nbsp;<span id="iseepgCount" class="text-danger count">-</span>
										</small></p>
									</div>
									<div class="col-md-2 ps-0">
										<button class="btn btn-secondary shadow" id="getCount" type="button">Get Count</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="associationDetails" class="d-none row text-start">
						<div class="col mt-2 shadow mx-auto p-2 bg-white border border-primary">
							<h6>Association Details:</h6>	
							<div class="row associationrow">
								<div class="col">
									<div class="mb-3">
										<label for="endpointDescription">Endpoint Description</label>
										<input type="text" class="form-control mt-2 mb-3 user-input shadow" value="" name="endpointDescription" id="endpointDescription" placeholder="Device Description">
									</div>
								</div>
							</div>
							<div class="row associationrow">
								<div class="col">
									<div class="mb-3">
										<label for="fullName">Full Name</label>
										<input type="text" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" value="" id="fullName" placeholder="John Smith">
										<div class="invalid-feedback">Please enter your Full Name</div>
									</div>
								</div>
							</div>
							<div class="row associationrow"> 
								<div class="col">
									<div class="mb-3">
										<label for="emailAddress">Email address</label>
										<input type="email" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" value="" id="emailAddress" placeholder="john@company.com">
										<div class="invalid-feedback">Please enter a valid email address</div>
									</div> 
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button id="bulkimport" module="endpoints" sub-module="bulkimport" class="btn btn-primary shadow" disabled>Import</button>
				<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	var failure;
	
	var ctrlActive = false;
	
	$("#bulkAddEndpointDialog").modal('show');

	$(function() {	
		feather.replace()
	});
	
	$("#csvFile").change(function () {
		$("#uploadCsv").removeAttr('disabled');
	});
	
	$("#bulkimport").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();

		if(failure){
			return false;
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('bulkAddEndpointDialog'));
			modal.hide();
		}
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				associationGroup: $("#associationGroup").val(),
				wirelessSSID: $("#wirelessSSID").val(),
				bulkImportType: $("#bulkImportType").val(),
				emailAddress: $("#emailAddress").val(),
				fullName: $("#fullName").val(),
				groupUuid: $("#groupUuid").val(),
				uploadkey: $("#uploadkey").val()
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
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
			url: "ajax/fileupload.php",
			
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
					$(".associationrow").addClass('d-none');
					$("#uploadMessage").html( data.message );
					$("#uploadMessage").addClass('text-success');
					$("#uploadMessage").removeClass('text-danger');
					$("#uploadMessage").removeClass('d-none');
					$("#bulkimport").removeAttr('disabled');
					$("#fullName").attr("validation-state", '');
					$("#emailAddress").attr("validation-state", '');
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
					$("#bulkimport").attr("disabled", true);
					$("#fullName").attr("validation-state", 'required');
					$("#emailAddress").attr("validation-state", 'required');					
				}
			}
		});
	});
	
	$("#bulkImportType").change(function(){
		event.preventDefault();
		
		$("#bulkimport").attr("disabled", true);
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
				url: "ajax/getdata.php",
			
				data: {
					'data-command': 'getdata',
					'data-set': 'get_endpoint_groups'
				},
				type: "POST",
				dataType: "json",
				success: function (epglist) {
					$("#groupUuid").find("option").remove(),
					$.each(epglist.SearchResult.resources, function(index, element) {
						temp = $('<option>', {value: element.id, description: element.description}),
						$("#groupUuid").append(temp.html(element.name));
					}),
					$("#iseBulkImport").removeClass('d-none');
					$("#associationDetails").removeClass('d-none');
					$("#groupUuid").trigger("change");
					$("#bulkimport").removeAttr('disabled');
				}
			});
			
		}
	});
		
	$("#getCount").click(function(){
		event.preventDefault();

		$.ajax({
			url: "ajax/getdata.php",
			
				data: {
					'data-command': 'getdata',
					'data-set': 'get_endpoint_count',
					'groupUuid': $("#groupUuid").find('option:selected').val()
				},
			type: "POST",
			dataType: "text",
			success: function (epCount) {
				$( "#iseepgCount" ).html( epCount );
			}
		});
	});	
	
	$("#groupUuid").change(function() {
		var description = "";
		$( "#groupUuid option:selected" ).each(function() {
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
HTML;

print $htmlbody;
?>