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
		$pageData['endpointGroupList'] .= '<select name="associationGroup" id="associationGroup" class="form-control shadow form-validation">';
		
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
		$pageData['wirelessSSIDList'] .= '<select name="wirelessSSID" id="wirelessSSID" class="form-control shadow form-validation">';
	
		while($row = $wirelessNetworks->fetch_assoc()) {		

			$pageData['wirelessSSIDList'] .= "<option value=\"".$row['id']."\">".$row['ssidName']."</option>";			
		}
		
		$pageData['wirelessSSIDList'] .= "</select>";
	}

	$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="addEndpointDialog" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Add Endpoint</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="needs-validation" novalidate>
					<div class="form-row">
						<div class="col m-2 shadow p-2 bg-white border border-primary">
							<h5 class="text-center">Endpoint Association Details</h5>
							<hr />
							
							<div class="form-group">
								<label class="font-weight-bold" for="associationGroup">Association Type:</label>
								{$pageData['endpointGroupList']}
								<div class="invalid-feedback">Please enter a valid MAC Address</div>
							</div>

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
							<div class="form-group">
								<label class="font-weight-bold" for="wirelessSSID">Wireless SSID:</label>
								{$pageData['wirelessSSIDList']}
								<div class="invalid-feedback">Please enter a valid MAC Address</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="font-weight-bold" for="macAddress">Endpoint MAC Address</label>
						<input type="text" class="form-control mt-2 mb-3 shadow user-input form-validation" validation-state="required" validation-minimum-length="17" validation-maximum-length="17" value="" id="macAddress" name="macAddress" maxlength="17" placeholder="XX:XX:XX:XX:XX:XX">
						<div class="invalid-feedback">Please enter a valid MAC Address</div>
					</div>

					<div class="form-group">
						<label class="font-weight-bold" for="endpointDescription">Endpoint Description</label>
						<input type="text" class="form-control mt-2 mb-3 user-input shadow" value="" name="endpointDescription" id="endpointDescription" placeholder="Device Description">
					</div>
										
					<div class="form-group">
						<label class="font-weight-bold" for="fullName">Full Name</label>
						<input type="text" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" value="" name="fullName" id="fullName" placeholder="John Smith">
						<div class="invalid-feedback">Please enter your Full Name</div>
					</div>
					
					<div class="form-group">
						<label class="font-weight-bold" for="emailAddress">Email address</label>
						<input type="email" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" value="" name="emailAddress" id="emailAddress" placeholder="john@company.com">
						<div class="invalid-feedback">Please enter a valid email address</div>
					</div>

				</div>
				<div class="modal-footer">
					<button id="create" module="endpoints" sub-module="create" class="btn btn-primary shadow" data-dismiss="modal">Create</button>
					<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	var failure;
	
	var ctrlActive = false;
	
	$("#addEndpointDialog").modal('show');

	$(function() {	
		feather.replace()
	});
	
	$("#create").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();

		if(failure){
			return false;
		}
		
		$("#addEndpointDialog").modal('hide');
		$('body').removeClass('modal-open');
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				associationGroup: $("#associationGroup").val(),
				macAddress: $("#macAddress").val(),
				endpointDescription: $("#endpointDescription").val(),
				emailAddress: $("#emailAddress").val(),
				fullName: $("#fullName").val()
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
HTML;

print $htmlbody;
?>