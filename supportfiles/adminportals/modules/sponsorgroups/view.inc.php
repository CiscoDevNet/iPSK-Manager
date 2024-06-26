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
				$sponsorGroupEPMembers .= '<span class="badge text-bg-primary m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">'.$row['groupName'].'</h6></span>';
			}
		}else{
			$sponsorGroupEPMembers = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$sponsorGroupEPMembers = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	if($wirelessNetworkListing){
		if($wirelessNetworkListing->num_rows != 0){
			while($row = $wirelessNetworkListing->fetch_assoc()){
				$wirelessNetworks .= '<span class="badge text-bg-success m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">'.$row['ssidName'].'</h6></span>';
			}
		}else{
			$wirelessNetworks = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$wirelessNetworks = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	if($internalGroupsListing){
		if($internalGroupsListing->num_rows != 0){
			while($row = $internalGroupsListing->fetch_assoc()){
				$groupPermissions = $row['groupPermissions'];
				$groupInternalGroups[$row['internalGroupId']] = $row['internalGroupName'];
				$authorizationGroups .= '<span class="badge text-bg-warning m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">'.$row['internalGroupName'].'</h6></span>';
			}
		}else{
			$authorizationGroups = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$authorizationGroups = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
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
							<div class="row text-center">
								<div class="col">
									<div class="form-check">
										<input type="checkbox" class="form-check-input checkbox-update" base-value="1024" value="{$checkBoxPermissions[1024]['value']}" id="portalPskEditCheck"{$checkBoxPermissions[1024]['check']} disabled>
										<label class="form-check-label text-danger" for="portalPskEditCheck"><strong>Allow Manual PSK Editing on Associations</strong></label>
									</div>
								</div>
							</div>
							
HTML;
	}else{
		$pskEdit = '';
	}

$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewSponsorGroup" tabindex="-1" role="dialog" aria-labelledby="viewSponsorGroupModal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Portal Group</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="name">Portal Group Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="name" value="{$sponsorGroups['sponsorGroupName']}" readonly>
		</div>
		<label class="fw-bold" for="authz">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="authz" value="{$sponsorGroups['sponsorGroupDescription']}" readonly>
		</div>
		<div class="row">
			<div class="col">
				<label class="fw-bold" for="authz">Group Type:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="authz" value="{$sponsorGroups['sponsorGroupType']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="authz">Authentication Type:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="authz" value="{$sponsorGroups['sponsorGroupAuthType']}" readonly>
				</div>
			</div>
		</div>
		<label class="fw-bold" for="endpointGroups">Endpoint Group Members:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="mb-3 fw-bold mb-0">		
				$sponsorGroupEPMembers
			</div>
		</div>
		<label class="fw-bold" for="wirelessNetworkMembers">Wireless Networks:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="mb-3 fw-bold mb-0">		
				$wirelessNetworks
			</div>
		</div>
		<label class="fw-bold" for="authorizationGroups">Authorization Groups:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="mb-3 fw-bold mb-0">		
				$authorizationGroups
			</div>
		</div>
		<div class="row">
			<div class="col m-2 shadow p-2 bg-white border border-primary">
				<div class="mb-3 fw-bold">
					<label class="fw-bold" for="viewPermission">View Permissions:</label>		
					<select class="form-select shadow" id="viewPermission" disabled>
						$viewPermissions
					</select>
					<small id="viewPermissionBlock" class="form-text text-muted">Choose View Permission Level</small>
				</div>
				<div class="form-check">
					<input type="checkbox" class="form-check-input checkbox-update" name="viewPassCheck" base-value="8" value="{$checkBoxPermissions[8]['value']}" id="viewPassCheck" disabled{$checkBoxPermissions[8]['check']}>
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
							<input type="checkbox" class="form-check-input checkbox-update" base-value="512" value="{$checkBoxPermissions[512]['value']}" id="createCheck"{$checkBoxPermissions[512]['check']} disabled>
							<label class="form-check-label" for="createCheck">Create Endpoint associations</label>
						</div>
						<div class="form-check">
							<input type="checkbox" class="form-check-input checkbox-update" base-value="2048" value="{$checkBoxPermissions[2048]['value']}" id="bulkCreateCheck"{$checkBoxPermissions[2048]['check']} disabled>
							<label class="form-check-label" for="bulkCreateCheck">Bulk Create Endpoint associations</label>
						</div>
						<div class="form-check">
							<input type="checkbox" class="form-check-input checkbox-update" base-value="256" value="{$checkBoxPermissions[256]['value']}" id="editCheck"{$checkBoxPermissions[256]['check']} disabled>
							<label class="form-check-label" for="editCheck">Edit the associated iPSK Endpoint</label>
						</div>
						<div class="form-check">
							<input type="checkbox" class="form-check-input checkbox-update" base-value="64" value="{$checkBoxPermissions[64]['value']}" id="deleteCheck"{$checkBoxPermissions[64]['check']} disabled>
							<label class="form-check-label" for="deleteCheck">Delete an associated iPSK Endpoint</label>
						</div>						
					</div>
					<div class="col">
						<div class="form-check">
							<input type="checkbox" class="form-check-input checkbox-update" base-value="128" value="{$checkBoxPermissions[128]['value']}" id="extendCheck"{$checkBoxPermissions[128]['check']} disabled>
							<label class="form-check-label" for="extendCheck">Extend an associated Endpoints Expiration date</label>
						</div>
						<div class="form-check">
							<input type="checkbox" class="form-check-input checkbox-update" base-value="32" value="{$checkBoxPermissions[32]['value']}" id="unsuspendCheck"{$checkBoxPermissions[32]['check']} disabled>
							<label class="form-check-label" for="unsuspendCheck">Reinstate an associated iPSK Suspended Endpoint</label>
						</div>
						<div class="form-check">
							<input type="checkbox" class="form-check-input checkbox-update" base-value="16" value="{$checkBoxPermissions[16]['value']}" id="suspendCheck"{$checkBoxPermissions[16]['check']} disabled>
							<label class="form-check-label" for="suspendCheck">Suspend an associated iPSK Endpoint's access</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label class="fw-bold" for="Max">Date Created:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="createdDate" value="{$sponsorGroups['createdDate']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="createdBy">Created By:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="createdBy" value="{$sponsorGroups['createdBy']}" readonly>
				</div>		
			</div>
		</div>

	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewSponsorGroup").modal('show');

	$(function() {	
		feather.replace()
	});
</script>
HTML;

print $htmlbody;
?>