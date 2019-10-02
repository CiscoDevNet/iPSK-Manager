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
	


	$id = filter_var($_POST['id'],FILTER_VALIDATE_INT);

	if($id > 0){

		$internalGroup = $ipskISEDB->getInternalGroupById($id);

		if($internalGroup['groupType'] == 1){
			$groupDn = '<label class="font-weight-bold" for="groupDn">External Group Distinguished Name:</label><div class="form-group input-group-sm font-weight-bold"><input type="text" class="form-control shadow" id="groupDn" value="'.$internalGroup['groupDn'].'" readonly></div>';	
		}else{
			$groupDn = "";
		}
		
		if($internalGroup['permissions'] == 1){
			$internalGroup['permissions'] = " checked";
		}else{
			$internalGroup['permissions'] = "";
		}

		$internalGroup['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($internalGroup['createdBy']);

		$internalGroup['createdDate'] = date($globalDateOutputFormat, strtotime($internalGroup['createdDate']));
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewInternalGroup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Group</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="groupName">Group Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="groupName" value="{$internalGroup['groupName']}" readonly>
		</div>
		<label class="font-weight-bold" for="description">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="description" value="{$internalGroup['description']}" readonly>
		</div>
		$groupDn
		<label class="font-weight-bold">Permissions:</label>
		<div class="form-row">
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" id="adminAccess" disabled{$internalGroup['permissions']}>
					<label class="custom-control-label" for="adminAccess">Admin Portal Access</label>
				</div>
			</div>
		</div>
		<label class="font-weight-bold" for="createdBy">Date Created:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$internalGroup['createdDate']}" readonly>
		</div>
		<label class="font-weight-bold" for="createdBy">Created By:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$internalGroup['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewInternalGroup").modal();

	$(function() {	
		feather.replace()
	});
</script>
HTML;

		print $htmlbody;
	}
?>