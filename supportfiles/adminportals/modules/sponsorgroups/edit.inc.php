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
	


	$groupPermissions = 0;
	$endpointGroups = "";
	$pageWirelessNetworks = "";
	$pageInternalGroups = "";
	$viewPermissions = "";
	
	$sponsorGroups = $ipskISEDB->getSponsorGroupById($sanitizedInput['id']);
	$sponsorGroupEPGroups = $ipskISEDB->getSponsorGroupEPGroups($sanitizedInput['id']);
	$wirelessNetworkListing = $ipskISEDB->getSponsorGroupWirelessNetworks($sanitizedInput['id']);
	$internalGroupsListing = $ipskISEDB->getSponsorGroupInternalGroups($sanitizedInput['id']);
	
	$endPointGroupListing = $ipskISEDB->getEndpointGroupListing();
	$wirelessNetworks = $ipskISEDB->getWirelessNetworks();
	$internalGroups = $ipskISEDB->getInternalGroups($sponsorGroups['sponsorGroupAuthType']);
	
	if($internalGroupsListing){
		while($row = $internalGroupsListing->fetch_assoc()){
			$groupPermissions = $row['groupPermissions'];
			$groupInternalGroups[$row['internalGroupId']] = $row['internalGroupName'];
		}
	}
	
	if($wirelessNetworkListing){
		while($row = $wirelessNetworkListing->fetch_assoc()){
			$groupWirelessNetworks[$row['wirelessNetworkId']] = $row['ssidName'];
		}
	}
	
	if($sponsorGroupEPGroups){
		while($row = $sponsorGroupEPGroups->fetch_assoc()){
			$groupEPGroups[$row['id']] = $row['groupName'];
		}
	}
	
	if($internalGroups){
		while($row = $internalGroups->fetch_assoc()){
			if(isset($groupInternalGroups[$row['id']])){
				$pageInternalGroups .= '<option value="'.$row['id'].'" selected>'.$row['groupName'].'</option>';
			}else{
				$pageInternalGroups .= '<option value="'.$row['id'].'">'.$row['groupName'].'</option>';
			}
		}
	}
	
	if($endPointGroupListing){
		while($row = $endPointGroupListing->fetch_assoc()){
			if(isset($groupEPGroups[$row['id']])){
				$endpointGroups .= '<option value="'.$row['id'].'" selected>'.$row['groupName'].'</option>';
			}else{
				$endpointGroups .= '<option value="'.$row['id'].'">'.$row['groupName'].'</option>';
			}
		}
	}

	if($wirelessNetworks){
		while($row = $wirelessNetworks->fetch_assoc()){
			if(isset($groupWirelessNetworks[$row['id']])){
				$pageWirelessNetworks .= '<option value="'.$row['id'].'" selected>'.$row['ssidName'].'</option>';
			}else{
				$pageWirelessNetworks .= '<option value="'.$row['id'].'">'.$row['ssidName'].'</option>';
			}
		}
	}
	
	if($groupPermissions & 1){
		$viewPermissions .= '<option value="1" selected>Only Endpoints owned by the user</option>';
	}else{
		$viewPermissions .= '<option value="1">Only Endpoints owned by the user</option>';
	}
	
	if($groupPermissions & 2){
		$viewPermissions .= '<option value="2" selected>Only Members of the Endpoint group</option>';
	}else{
		$viewPermissions .= '<option value="2">Only Members of the Endpoint group</option>';
	}
	
	if($groupPermissions & 4){
		$viewPermissions .= '<option value="4" selected>All Endpoint Associations</option>';
	}else{
		$viewPermissions .= '<option value="4">All Endpoint Associations</option>';
	}
	
	$baseValue = 4;

	for($i = 1; $i < 10; $i++){
		$baseValue = $baseValue << 1;
		
		if($baseValue & $groupPermissions){
			$checkBoxPermissions[$baseValue]['check'] = " checked";
			$checkBoxPermissions[$baseValue]['value'] = $baseValue;
		}else{
			$checkBoxPermissions[$baseValue]['check'] = "";
			$checkBoxPermissions[$baseValue]['value'] = "";
		}
	}
	
	if($sponsorGroups['sponsorGroupType'] == 0){
		$pageGroupType = '<option value="0" selected>Sponsor Group</option>';
		$pageGroupType .= '<option value="1">Non-Sponsor Group</option>';
		$groupTypeMultiFlag = " multiple";
	}else{
		$pageGroupType = '<option value="0">Sponsor Group</option>';
		$pageGroupType .= '<option value="1" selected>Non-Sponsor Group</option>';
		$groupTypeMultiFlag = "";
	}
	
	if($sponsorGroups['sponsorGroupAuthType'] == 1){
		$pageSponsorGroupAuthType = '<option value="0">Internal Authentication</option>';
		$pageSponsorGroupAuthType .= '<option value="1" selected>External Authentication</option>';
		
	}else{
		$pageSponsorGroupAuthType = '<option value="0" selected>Internal Authentication</option>';
		$pageSponsorGroupAuthType .= '<option value="1">External Authentication</option>';
	}
	
	$enablePskEdit = $ipskISEDB->getGlobalSetting("advanced-settings","enable-portal-psk-edit");
	
	if($enablePskEdit){	
		$pskEdit = <<< HTML
							<div class="form-row text-center">
								<div class="col">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="1024" value="{$checkBoxPermissions[1024]['value']}" id="portalPskEditCheck"{$checkBoxPermissions[1024]['check']}>
										<label class="custom-control-label text-danger" for="portalPskEditCheck"><strong>Allow Manual PSK Editing on Associations</strong></label>
									</div>						
								</div>
							</div>
							
HTML;
	}else{
		$pskEdit = '<input type="hidden" value="0" id="portalPskEditCheck">';
	}
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="editSponsorGroup" tabindex="-1" role="dialog" aria-labelledby="editSponsorGroupModal" aria-hidden="true">
	<form class="needs-validation" novalidate>
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">Edit Portal Group</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-row">
						<div class="col-4">
							<div class="form-group input-group-sm font-weight-bold">
								<label class="font-weight-bold" for="sponsorGroupName">Portal Group Name:</label>
								<input type="text" class="form-control shadow form-validation" validation-state="required" id="sponsorGroupName" value="{$sponsorGroups['sponsorGroupName']}" required>
								<small id="sponsorGroupNameBlock" class="form-text text-muted">Group Name is Required</small>
							</div>
						</div>
						<div class="col">
							<div class="form-group input-group-sm font-weight-bold">
								<label class="font-weight-bold" for="maxDevices">Max Devices:</label>
								<input type="text" class="form-control shadow form-validation" validation-state="required" id="maxDevices" value="{$sponsorGroups['maxDevices']}" required>
							</div>
						</div>
						<div class="col-6">
							<div class="form-group input-group-sm font-weight-bold">
								<label class="font-weight-bold" for="sponsorGroupAuthType">Group Authentication Type:</label>		
								<select class="form-control shadow" id="sponsorGroupAuthType" data-command="getdata" data-set="internalgroups">
									$pageSponsorGroupAuthType
								</select>
								<small id="sponsorGroupAuthTypeBlock" class="form-text text-muted">Choose Authentication Type</small>
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="col">
							<div class="form-group">
								<label class="font-weight-bold" for="sponsorGroupDescription">Description:</label>
								<textarea class="form-control shadow" id="sponsorGroupDescription" rows="3">{$sponsorGroups['sponsorGroupDescription']}</textarea>
							</div>
						</div>
						<div class="col">
							<div class="form-group font-weight-bold">
								<label class="font-weight-bold" for="authorizationGroups">Authorization Groups:</label>		
								<select class="form-control shadow form-validation" validation-state="required" id="authorizationGroups" multiple>
									$pageInternalGroups
								</select>
								<small id="authorizationGroupsBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="form-group input-group-sm font-weight-bold">
								<label class="font-weight-bold" for="sponsorGroupType">Portal Group Type:</label>		
								<select class="form-control shadow" id="sponsorGroupType" data-command="getdata" data-set="internalgroups">
									$pageGroupType
								</select>
								<small id="sponsorGroupTypeBlock" class="form-text text-muted">Choose Group Type</small>
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="col">
							<div class="form-group font-weight-bold">
								<label class="font-weight-bold" for="endpointGroupMembers">Endpoint Group Members:</label>	
								<select class="form-control shadow form-validation" validation-state="required" id="endpointGroupMembers"$groupTypeMultiFlag>
									$endpointGroups
								</select>
								<small id="endpointGroupMembersBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
							</div>
						</div>
						<div class="col">
							<div class="form-group font-weight-bold">
								<label class="font-weight-bold" for="wirelessNetworkMembers">Wireless Networks:</label>
								<select class="form-control shadow form-validation" validation-state="required" id="wirelessNetworkMembers"$groupTypeMultiFlag>
									$pageWirelessNetworks
								</select>
								<small id="wirelessNetworkMembersBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="col m-2 shadow p-2 bg-white border border-primary">
							<div class="form-group font-weight-bold">
								<label class="font-weight-bold" for="viewPermission">View Permissions:</label>		
								<select class="form-control shadow" id="viewPermission">
									$viewPermissions
								</select>
								<small id="viewPermissionBlock" class="form-text text-muted">Choose View Permission Level</small>
							</div>
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input checkbox-update" name="viewPassCheck" base-value="8" value="{$checkBoxPermissions[8]['value']}" id="viewPassCheck"{$checkBoxPermissions[8]['check']}>
								<label class="custom-control-label" for="viewPassCheck">Allow Viewing of Pre-Shared Keys <strong>(Only applies to selection above)</strong></label>
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="col m-2 shadow p-2 bg-white border border-primary">
							<h5 class="text-center">Permissions for Selected Endpoint Groups</h5>
							<hr />
							$pskEdit
							<div class="form-row">
								<div class="col">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="512" value="{$checkBoxPermissions[512]['value']}" id="createCheck"{$checkBoxPermissions[512]['check']}>
										<label class="custom-control-label" for="createCheck">Create Endpoint associations</label>
									</div>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="2048" value="{$checkBoxPermissions[2048]['value']}" id="bulkCreateCheck"{$checkBoxPermissions[2048]['check']}>
										<label class="custom-control-label" for="bulkCreateCheck">Bulk Create Endpoint associations</label>
									</div>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="256" value="{$checkBoxPermissions[256]['value']}" id="editCheck"{$checkBoxPermissions[256]['check']}>
										<label class="custom-control-label" for="editCheck">Edit the associated iPSK Endpoint</label>
									</div>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="64" value="{$checkBoxPermissions[64]['value']}" id="deleteCheck"{$checkBoxPermissions[64]['check']}>
										<label class="custom-control-label" for="deleteCheck">Delete an associated iPSK Endpoint</label>
									</div>						
								</div>
								<div class="col">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="128" value="{$checkBoxPermissions[128]['value']}" id="extendCheck"{$checkBoxPermissions[128]['check']}>
										<label class="custom-control-label" for="extendCheck">Extend an associated Endpoints Expiration date</label>
									</div>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="32" value="{$checkBoxPermissions[32]['value']}" id="unsuspendCheck"{$checkBoxPermissions[32]['check']}>
										<label class="custom-control-label" for="unsuspendCheck">Reinstate an associated iPSK Suspended Endpoint</label>
									</div>
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="16" value="{$checkBoxPermissions[16]['value']}" id="suspendCheck"{$checkBoxPermissions[16]['check']}>
										<label class="custom-control-label" for="suspendCheck">Suspend an associated iPSK Endpoint's access</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" id="id" value="{$sponsorGroups['id']}">
					<button id="update" module="sponsorgroups" sub-module="update" type="submit" class="btn btn-primary shadow">Update</button>
					<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
				</div>
				
			</div>
		</div>
	</form>
</div>
<script>
	var failure;
	
	$("#editSponsorGroup").modal();

	$(function() {	
		feather.replace()
	});
	
	$("#update").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}		
		
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
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

print $htmlbody;
?>