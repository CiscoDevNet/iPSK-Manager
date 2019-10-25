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
	

	$groupPermissions = 0;
	$sponsorGroupEPMembers = "";
	$wirelessNetworks = "";
	$authorizationGroups = "";
	$viewPermissions = "";

	$sponsorGroups = $ipskISEDB->getSponsorGroupById($sanitizedInput['id']);
	$sponsorGroupEPGroups = $ipskISEDB->getSponsorGroupEPGroups($sanitizedInput['id']);
	$wirelessNetworkListing = $ipskISEDB->getSponsorGroupWirelessNetworks($sanitizedInput['id']);
	$internalGroupsListing = $ipskISEDB->getSponsorGroupInternalGroups($sanitizedInput['id']);

	if($sponsorGroupEPGroups){
		if($sponsorGroupEPGroups->num_rows != 0){
			while($row = $sponsorGroupEPGroups->fetch_assoc()){
				$sponsorGroupEPMembers .= '<span class="badge badge-primary m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">'.$row['groupName'].'</h6></span>';
			}
		}else{
			$sponsorGroupEPMembers = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$sponsorGroupEPMembers = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	if($wirelessNetworkListing){
		if($wirelessNetworkListing->num_rows != 0){
			while($row = $wirelessNetworkListing->fetch_assoc()){
				$wirelessNetworks .= '<span class="badge badge-success m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">'.$row['ssidName'].'</h6></span>';
			}
		}else{
			$wirelessNetworks = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$wirelessNetworks = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	if($internalGroupsListing){
		if($internalGroupsListing->num_rows != 0){
			while($row = $internalGroupsListing->fetch_assoc()){
				$groupPermissions = $row['groupPermissions'];
				$groupInternalGroups[$row['internalGroupId']] = $row['internalGroupName'];
				$authorizationGroups .= '<span class="badge badge-warning m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">'.$row['internalGroupName'].'</h6></span>';
			}
		}else{
			$authorizationGroups = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$authorizationGroups = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
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

	$sponsorGroups['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($sponsorGroups['createdBy']);
	
	$sponsorGroups['createdDate'] = date($globalDateOutputFormat, strtotime($sponsorGroups['createdDate']));
	
	if($sponsorGroups['sponsorGroupAuthType'] == 0){
		$sponsorGroups['sponsorGroupAuthType'] = "Internal Authentication";
	}else{
		$sponsorGroups['sponsorGroupAuthType'] = "External Authentication";
	}
	
	if($sponsorGroups['sponsorGroupType'] == 0){
		$sponsorGroups['sponsorGroupType'] = "Sponsor";
	}else{
		$sponsorGroups['sponsorGroupType'] = "Non-Sponsor";
	}

	$enablePskEdit = $ipskISEDB->getGlobalSetting("advanced-settings","enable-portal-psk-edit");
	
	if($enablePskEdit){	
		$pskEdit = <<< HTML
							<div class="form-row text-center">
								<div class="col">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input checkbox-update" base-value="1024" value="{$checkBoxPermissions[1024]['value']}" id="portalPskEditCheck"{$checkBoxPermissions[1024]['check']} disabled>
										<label class="custom-control-label text-danger" for="portalPskEditCheck"><strong>Allow Manual PSK Editing on Associations</strong></label>
									</div>
								</div>
							</div>
							
HTML;
	}else{
		$pskEdit = '';
	}

$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewSponsorGroup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Portal Group</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="name">Portal Group Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="name" value="{$sponsorGroups['sponsorGroupName']}" readonly>
		</div>
		<label class="font-weight-bold" for="authz">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="authz" value="{$sponsorGroups['sponsorGroupDescription']}" readonly>
		</div>
		<div class="row">
			<div class="col">
				<label class="font-weight-bold" for="authz">Group Type:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="authz" value="{$sponsorGroups['sponsorGroupType']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="font-weight-bold" for="authz">Authentication Type:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="authz" value="{$sponsorGroups['sponsorGroupAuthType']}" readonly>
				</div>
			</div>
		</div>
		<label class="font-weight-bold" for="endpointGroups">Endpoint Group Members:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="form-group font-weight-bold mb-0">		
				$sponsorGroupEPMembers
			</div>
		</div>
		<label class="font-weight-bold" for="wirelessNetworkMembers">Wireless Networks:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="form-group font-weight-bold mb-0">		
				$wirelessNetworks
			</div>
		</div>
		<label class="font-weight-bold" for="authorizationGroups">Authorization Groups:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="form-group font-weight-bold mb-0">		
				$authorizationGroups
			</div>
		</div>
		<div class="form-row">
			<div class="col m-2 shadow p-2 bg-white border border-primary">
				<div class="form-group font-weight-bold">
					<label class="font-weight-bold" for="viewPermission">View Permissions:</label>		
					<select class="form-control shadow" id="viewPermission" disabled>
						$viewPermissions
					</select>
					<small id="viewPermissionBlock" class="form-text text-muted">Choose View Permission Level</small>
				</div>
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" name="viewPassCheck" base-value="8" value="{$checkBoxPermissions[8]['value']}" id="viewPassCheck" disabled{$checkBoxPermissions[8]['check']}>
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
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="512" value="{$checkBoxPermissions[512]['value']}" id="createCheck"{$checkBoxPermissions[512]['check']} disabled>
							<label class="custom-control-label" for="createCheck">Create Endpoint associations</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="2048" value="{$checkBoxPermissions[2048]['value']}" id="bulkCreateCheck"{$checkBoxPermissions[2048]['check']} disabled>
							<label class="custom-control-label" for="bulkCreateCheck">Bulk Create Endpoint associations</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="256" value="{$checkBoxPermissions[256]['value']}" id="editCheck"{$checkBoxPermissions[256]['check']} disabled>
							<label class="custom-control-label" for="editCheck">Edit the associated iPSK Endpoint</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="64" value="{$checkBoxPermissions[64]['value']}" id="deleteCheck"{$checkBoxPermissions[64]['check']} disabled>
							<label class="custom-control-label" for="deleteCheck">Delete an associated iPSK Endpoint</label>
						</div>						
					</div>
					<div class="col">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="128" value="{$checkBoxPermissions[128]['value']}" id="extendCheck"{$checkBoxPermissions[128]['check']} disabled>
							<label class="custom-control-label" for="extendCheck">Extend an associated Endpoints Expiration date</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="32" value="{$checkBoxPermissions[32]['value']}" id="unsuspendCheck"{$checkBoxPermissions[32]['check']} disabled>
							<label class="custom-control-label" for="unsuspendCheck">Reinstate an associated iPSK Suspended Endpoint</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="16" value="{$checkBoxPermissions[16]['value']}" id="suspendCheck"{$checkBoxPermissions[16]['check']} disabled>
							<label class="custom-control-label" for="suspendCheck">Suspend an associated iPSK Endpoint's access</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label class="font-weight-bold" for="Max">Date Created:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="createdDate" value="{$sponsorGroups['createdDate']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="font-weight-bold" for="createdBy">Created By:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="createdBy" value="{$sponsorGroups['createdBy']}" readonly>
				</div>		
			</div>
		</div>

	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewSponsorGroup").modal();

	$(function() {	
		feather.replace()
	});
</script>
HTML;

print $htmlbody;
?>