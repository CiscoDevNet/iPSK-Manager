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
	


	$endPointGroups = $ipskISEDB->getEndpointGroupById($sanitizedInput['id']);
	
	$endPointGroups['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($endPointGroups['createdBy']);
	
	$endPointGroups['createdDate'] = date($globalDateOutputFormat, strtotime($endPointGroups['createdDate']));
	
	if($endPointGroups['enabled'] == 1){
		$endPointGroups['enabled'] = " checked";
	}else{
		$endPointGroups['enabled'] = "";
	}
	
	if($endPointGroups['notificationPermission'] == 1){
		$endPointGroups['notificationPermission'] = " checked";
	}else{
		$endPointGroups['notificationPermission'] = "";
	}
	
	
	if($endPointGroups['ciscoAVPairPSK'] == "*userrandom*"){
		$endPointGroups['ciscoAVPairPSK'] = "Randomly Chosen per User";
	}elseif($endPointGroups['ciscoAVPairPSK'] == "*devicerandom*"){
		$endPointGroups['ciscoAVPairPSK'] = "Randomly Chosen per Device";
	}

 	if($endPointGroups['termLengthSeconds'] == 0){
		$endPointGroups['termLengthSeconds'] = "No Expiration";
	}else{
		if(($endPointGroups['termLengthSeconds'] / 31536000) >= 1){
			$endPointGroups['termLengthSeconds'] = ($endPointGroups['termLengthSeconds'] / 31536000)." Years";
		}elseif(($endPointGroups['termLengthSeconds'] / 2592000) >= 1){
			$endPointGroups['termLengthSeconds'] = ($endPointGroups['termLengthSeconds'] / 2592000)." Months";
		}elseif(($endPointGroups['termLengthSeconds'] / 604800) >= 1){
			$endPointGroups['termLengthSeconds'] = ($endPointGroups['termLengthSeconds'] / 604800)." Weeks";
		}elseif(($endPointGroups['termLengthSeconds'] / 86400) >= 1){
			$endPointGroups['termLengthSeconds'] = ($endPointGroups['termLengthSeconds'] / 86400)." Days";
		}else{
			$endPointGroups['termLengthSeconds'] = ($endPointGroups['termLengthSeconds'] / 86400)." Days";
		}
	}
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewepggroup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Endpoint Grouping</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="groupName">iPSK Endpoint Group Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="groupName" value="{$endPointGroups['groupName']}" readonly>
		</div>
		<label class="font-weight-bold" for="description">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="description" value="{$endPointGroups['description']}" readonly>
		</div>
		<label class="font-weight-bold" for="authzPolicyName">Authorization Template:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="authzPolicyName" value="{$endPointGroups['authzPolicyName']}" readonly>
		</div>
		<div class="input-group input-group-sm mb-3">
			<div class="input-group-prepend">
				<span class="input-group-text font-weight-bold shadow" id="basic-addon1">Description</span>
			</div>
			<input type="text" class="form-control shadow" value="{$endPointGroups['authzPolicyDescription']}" aria-label="text" aria-describedby="basic-addon1" readonly>
		</div>
		<div class="input-group input-group-sm mb-3 shadow">
			<div class="input-group-prepend">
				<span class="input-group-text font-weight-bold shadow" id="basic-addon1">Pre-Shared Key</span>
			</div>
			<input type="password" id="presharedKey" class="form-control shadow" value="{$endPointGroups['ciscoAVPairPSK']}" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true" readonly>
			<div class="input-group-append">
				<span class="input-group-text font-weight-bold shadow" id="basic-addon1"><a id="showpassword" href="#"><span id="passwordfeather" data-feather="eye"></span></a></span>
			</div>
		</div>
		<div class="input-group input-group-sm mb-3">
			<div class="input-group-prepend">
				<span class="input-group-text font-weight-bold shadow" id="basic-addon1">Access Term Length</span>
			</div>
			<input type="text" class="form-control shadow" value="{$endPointGroups['termLengthSeconds']}" aria-label="term" aria-describedby="basic-addon1" readonly>
		</div>
		<div class="form-row">
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" name="viewGroupEnabled" id="viewPassCheck" disabled{$endPointGroups['enabled']}>
					<label class="custom-control-label" for="viewGroupEnabled">Enabled</label>
				</div>					
			</div>
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" id="viewNotifyPermissions" disabled{$endPointGroups['notificationPermission']}>
					<label class="custom-control-label" for="viewNotifyPermissions">Email Notifications</label>
				</div>
			</div>
		</div>	
		
		<label class="font-weight-bold" for="createdDate">Date Created:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdDate" value="{$endPointGroups['createdDate']}" readonly>
		</div>
		<label class="font-weight-bold" for="createdBy">Created By:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$endPointGroups['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewepggroup").modal();

	$(function() {	
		feather.replace()
	});
	$("#showpassword").on('click', function(event) {
		event.preventDefault();
		if($("#presharedKey").attr('type') == "text"){
			$("#presharedKey").attr('type', 'password');
			$("#passwordfeather").attr('data-feather','eye');
			feather.replace();
		}else if($("#presharedKey").attr('type') == "password"){
			$("#presharedKey").attr('type', 'text');
			$("#passwordfeather").attr('data-feather','eye-off');
			feather.replace();
		}
	});
</script>
HTML;

print $htmlbody;
?>