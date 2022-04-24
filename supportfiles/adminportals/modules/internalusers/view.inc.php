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
	


	$id = filter_var($_POST['id'],FILTER_VALIDATE_INT);

	if($id > 0){

		$internalUser = $ipskISEDB->getInternalUserById($id);
		
		if($internalUser['enabled'] == 1){
			$internalUser['enabled'] = " checked";
		}else{
			$internalUser['enabled'] = "";
		}

		$internalUser['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($internalUser['createdBy']);

		$internalUser['createdDate'] = date($globalDateOutputFormat, strtotime($internalUser['createdDate']));
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewInternalUser" tabindex="-1" role="dialog" aria-labelledby="viewInternalUserModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="groupName">Username:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="userName" value="{$internalUser['userName']}" readonly>
		</div>
		<label class="font-weight-bold" for="fullName">Full Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="fullName" value="{$internalUser['fullName']}" readonly>
		</div>
		<label class="font-weight-bold" for="description">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="description" value="{$internalUser['description']}" readonly>
		</div>
		<label class="font-weight-bold" for="email">Email Address:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="email" value="{$internalUser['email']}" readonly>
		</div>
		<label class="font-weight-bold" for="dn">Distinguished Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="dn" value="{$internalUser['dn']}" readonly>
		</div>
		<label class="font-weight-bold" for="sid">SID:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="sid" value="{$internalUser['sid']}" readonly>
		</div>
		<label class="font-weight-bold">Account Options:</label>
		<div class="form-row">
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" id="adminAccess" disabled{$internalUser['enabled']}>
					<label class="custom-control-label" for="adminAccess">Account Enabled</label>
				</div>
			</div>
		</div>
		<label class="font-weight-bold" for="createdBy">Date Created:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$internalUser['createdDate']}" readonly>
		</div>
		<label class="font-weight-bold" for="createdBy">Created By:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$internalUser['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewInternalUser").modal();

	$(function() {	
		feather.replace()
	});
</script>
HTML;

		print $htmlbody;
	}
?>