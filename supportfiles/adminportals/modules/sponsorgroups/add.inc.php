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
	


	$endpointGroups = "";
	$wirelessNetworks = "";
	$authZGroups = "";
	$userMessage = "";
	$dependencyMissing = false;
	
	$endPointGroupListing = $ipskISEDB->getEndpointGroupListing();
	$wirelessNetworkListing = $ipskISEDB->getWirelessNetworks();
	$internalGroups = $ipskISEDB->getInternalGroups('1');
	$enablePskEdit = $ipskISEDB->getGlobalSetting("advanced-settings","enable-portal-psk-edit");
	
	if($endPointGroupListing){
		while($row = $endPointGroupListing->fetch_assoc()){
			$endpointGroups .= '<option value="'.$row['id'].'">'.$row['groupName'].'</option>';
		}
	}else{
		$userMessage = "Endpoint Grouping missing<br /><br />";
	}

	if($wirelessNetworkListing){
		while($row = $wirelessNetworkListing->fetch_assoc()){
			$wirelessNetworks .= '<option value="'.$row['id'].'">'.$row['ssidName'].'</option>';
		}
	}else{
		$userMessage .= "Wireless Network missing";
	}
	
	if($internalGroups){
		while($row = $internalGroups->fetch_assoc()){
			$authZGroups .= '<option value="'.$row['id'].'">'.$row['groupName'].'</option>';
		}
	}

	if($enablePskEdit){
		$pskEdit = <<< HTML
							<div class="row text-center">
								<div class="col">
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="1024" value="" id="portalPskEditCheck">
										<label class="form-check-label text-danger" for="portalPskEditCheck"><strong>Allow Manual PSK Editing on Associations</strong></label>
									</div>						
								</div>
							</div>
							
HTML;
	}else{
		$pskEdit = '<input type="hidden" value="" id="portalPskEditCheck">';
	}

	if($endPointGroupListing && $wirelessNetworkListing){

		$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="addSponsorGroup" tabindex="-1" role="dialog" aria-labelledby="addSponsorGroupModal" aria-hidden="true">
	<form class="needs-validation" novalidate>
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">Add Portal Group</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-4">
							<div class="mb-3 input-group-sm fw-bold">
								<label class="fw-bold" for="sponsorGroupName">Portal Group Name:</label>
								<input type="text" class="form-control shadow form-validation" validation-state="required" id="sponsorGroupName" value="" required>
								<small id="sponsorGroupNameBlock" class="form-text text-muted">Group Name is Required</small>
							</div>
						</div>
						<div class="col">
							<div class="mb-3 input-group-sm fw-bold">
								<label class="fw-bold" for="maxDevices">Max Devices:</label>
								<input type="text" class="form-control shadow form-validation" validation-state="required" id="maxDevices" value="5" required>
							</div>
						</div>
						<div class="col-6">
							<div class="mb-3 input-group-sm fw-bold">
								<label class="fw-bold" for="sponsorGroupAuthType">Group Authentication Type:</label>		
								<select class="form-select shadow" id="sponsorGroupAuthType" data-command="getdata" data-set="internalgroups">
									<option value="0">Internal Authentication</option>
									<option value="1" selected>External Authentication</option>
								</select>
								<small id="sponsorGroupAuthTypeBlock" class="form-text text-muted">Choose Authentication Type</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="mb-3">
								<label class="fw-bold" for="sponsorGroupDescription">Description:</label>
								<textarea class="form-control shadow" id="sponsorGroupDescription" rows="3"></textarea>
							</div>
						</div>
						<div class="col">
							<div class="mb-3 fw-bold">
								<label class="fw-bold" for="authorizationGroups">Authorization Groups:</label>		
								<select class="form-select shadow form-validation" validation-state="required" id="authorizationGroups" multiple>
									$authZGroups
								</select>
								<small id="authorizationGroupsBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="mb-3 input-group-sm fw-bold">
								<label class="fw-bold" for="sponsorGroupType">Sponsor Group Type:</label>		
								<select class="form-select shadow" id="sponsorGroupType" data-command="getdata" data-set="internalgroups">
									<option value="0" selected>Sponsor Group</option>
									<option value="1">Non-Sponsor Group</option>
								</select>
								<small id="sponsorGroupTypeBlock" class="form-text text-muted">Choose Group Type</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="mb-3 fw-bold">
								<label class="fw-bold" for="endpointGroupMembers">Endpoint Group Members:</label>	
								<select class="form-select shadow form-validation" validation-state="required" id="endpointGroupMembers" multiple>
									$endpointGroups
								</select>
								<small id="endpointGroupMembersBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
							</div>
						</div>
						<div class="col">
							<div class="mb-3 fw-bold">
								<label class="fw-bold" for="wirelessNetworkMembers">Wireless Networks:</label>
								<select class="form-select shadow form-validation" validation-state="required" id="wirelessNetworkMembers" multiple>
									$wirelessNetworks
								</select>
								<small id="wirelessNetworkMembersBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col m-2 shadow p-2 bg-white border border-primary">
							<div class="mb-3 fw-bold">
								<label class="fw-bold" for="viewPermission">View Permissions:</label>		
								<select class="form-select shadow" id="viewPermission">
									<option value="1">Only Endpoints owned by the user</option>
									<option value="2">Only Members of the Endpoint group</option>
									<option value="4">All Endpoint Associations</option>
								</select>
								<small id="viewPermissionBlock" class="form-text text-muted">Choose View Permission Level</small>
							</div>
							<div class="form-check">
								<input type="checkbox" class="form-check-input checkbox-update" name="viewPassCheck" base-value="8" value="8" id="viewPassCheck" checked>
								<label class="form-check-label" for="viewPassCheck">Allow Viewing of Pre-Shared Keys <strong>(Only applies to selection above)</strong></label>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col m-2 shadow p-2 bg-white border border-primary">
							<h5 class="text-center">Permissions for Selected Endpoint Groups</h5>
							<hr />
							$pskEdit
							<div class="row">
								<div class="col">
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="512" value="512" id="createCheck" checked>
										<label class="form-check-label" for="createCheck">Create Endpoint associations</label>
									</div>
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="2048" value="" id="bulkCreateCheck">
										<label class="form-check-label" for="bulkCreateCheck">Bulk Create Endpoint associations</label>
									</div>
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="256" value="256" id="editCheck" checked>
										<label class="form-check-label" for="editCheck">Edit the associated iPSK Endpoint</label>
									</div>
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="64" value="64" id="deleteCheck" checked>
										<label class="form-check-label" for="deleteCheck">Delete an associated iPSK Endpoint</label>
									</div>							
								</div>
								<div class="col">
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="128" value="" id="extendCheck">
										<label class="form-check-label" for="extendCheck">Extend an associated Endpoints Expiration date</label>
									</div>
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="32" value="" id="unsuspendCheck">
										<label class="form-check-label" for="unsuspendCheck">Reinstate an associated iPSK Suspended Endpoint</label>
									</div>
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="16" value="" id="suspendCheck">
										<label class="form-check-label" for="suspendCheck">Suspend an associated iPSK Endpoint's access</label>
									</div>
								</div>
							</div>
						</div>
					</div>	
				</div>
				<div class="modal-footer">
					<button id="create" module="sponsorgroups" sub-module="create" type="submit" class="btn btn-primary shadow">Create</button>
					<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
				</div>
				
			</div>
		</div>
	</form>
</div>
<script>
	var failure;
	
	$("#addSponsorGroup").modal('show');

	$(function() {	
		feather.replace()
	});
	
	$("#create").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('addSponsorGroup'));
			modal.hide();
		}
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				sponsorGroupName: $("#sponsorGroupName").val(),
				sponsorGroupDescription: $("#sponsorGroupDescription").val(),
				sponsorGroupAuthType: $("#sponsorGroupAuthType").val(),
				sponsorGroupType: $("#sponsorGroupType").val(),
				maxDevices: $("#maxDevices").val(),
				endpointGroupMembers: $("#endpointGroupMembers").val(),
				wirelessNetworkMembers: $("#wirelessNetworkMembers").val(),
				authorizationGroups: $("#authorizationGroups").val(),
				suspendCheck: $("#suspendCheck").val(),
				unsuspendCheck: $("#unsuspendCheck").val(),
				extendCheck: $("#extendCheck").val(),
				deleteCheck: $("#deleteCheck").val(),
				editCheck: $("#editCheck").val(),
				createCheck: $("#createCheck").val(),
				viewPassCheck: $("#viewPassCheck").val(),
				viewPermission: $("#viewPermission").val(),
				bulkCreateCheck: $("#bulkCreateCheck").val(),
				portalPskEditCheck: $("#portalPskEditCheck").val()
				
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			}
		});
	});
	
	$(".checkbox-update").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));		
		}else{
			$(this).attr('value', '0');
		}
	});
	
	$("#sponsorGroupType").change(function(){
		event.preventDefault();
		
		if($("#sponsorGroupType").val() == 1){
			$("#wirelessNetworkMembers").removeAttr('multiple');
			$("#endpointGroupMembers").removeAttr('multiple');
		}else{
			$("#wirelessNetworkMembers").attr({multiple: 'multiple'});
			$("#endpointGroupMembers").attr({multiple: 'multiple'});
		}
	});
	
	$("#sponsorGroupAuthType").change(function(){
		event.preventDefault();
		
		$.ajax({
			url: "ajax/getdata.php",
			data: {
				'data-command': $(this).attr('data-command'),
				'data-set': $(this).attr('data-set'),
				'id': $(this).find('option:selected').val()
			},
			type: "POST",
			dataType: "json",
			success: function (data) {
				$("#authorizationGroups").find("option").remove(),
				$.each(data, function(index, element) {
					temp = $('<option>', {value: index}),
					$("#authorizationGroups").append(temp.html(element));
					});
			}
		});
	});
	
</script>
HTML;

	}else{
		$htmlbody = <<< HTML
		<div class="modal fade" id="missingDependencies" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header shadow alert alert-danger">
						<h5 class="modal-title fw-bold" id="modalLongTitle">Missing Dependencies</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						  
						</button>
					</div>
					<div class="modal-body">
						<p class="h6">$userMessage</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
		<script>
			var failure;
			
			$("#missingDependencies").modal('show');

			$(function() {	
				feather.replace()
			});
		</script>
HTML;
	}

print $htmlbody;
?>