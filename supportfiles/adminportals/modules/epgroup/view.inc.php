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
<div class="modal fade" id="viewepggroup" tabindex="-1" role="dialog" aria-labelledby="viewepggroupModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Endpoint Grouping</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="groupName">iPSK Endpoint Group Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="groupName" value="{$endPointGroups['groupName']}" readonly>
		</div>
		<label class="fw-bold" for="description">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="description" value="{$endPointGroups['description']}" readonly>
		</div>
		<label class="fw-bold" for="authzPolicyName">Authorization Template:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="authzPolicyName" value="{$endPointGroups['authzPolicyName']}" readonly>
		</div>
		<div class="input-group input-group-sm mb-3">
			<div class="input-group-prepend">
				<span class="input-group-text fw-bold shadow" id="basic-addon1">Description</span>
			</div>
			<input type="text" class="form-control shadow" value="{$endPointGroups['authzPolicyDescription']}" aria-label="text" aria-describedby="basic-addon1" readonly>
		</div>
		<div class="input-group input-group-sm mb-3 shadow">
			<div class="input-group-prepend">
				<span class="input-group-text fw-bold shadow" id="basic-addon1">Pre-Shared Key</span>
			</div>
			<input type="password" id="presharedKey" class="form-control shadow" value="{$endPointGroups['ciscoAVPairPSK']}" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true" readonly>
			<div class="input-group-append">
				<span class="input-group-text fw-bold shadow" id="basic-addon1"><a id="showpassword" href="#"><span id="passwordfeather" data-feather="eye"></span></a></span>
			</div>
		</div>
		<div class="input-group input-group-sm mb-3">
			<div class="input-group-prepend">
				<span class="input-group-text fw-bold shadow" id="basic-addon1">Access Term Length</span>
			</div>
			<input type="text" class="form-control shadow" value="{$endPointGroups['termLengthSeconds']}" aria-label="term" aria-describedby="basic-addon1" readonly>
		</div>
		<div class="row">
			<div class="col">
				<div class="form-check">
					<input type="checkbox" class="form-check-input checkbox-update" name="viewGroupEnabled" id="viewPassCheck" disabled{$endPointGroups['enabled']}>
					<label class="form-check-label" for="viewGroupEnabled">Enabled</label>
				</div>					
			</div>
			<div class="col">
				<div class="form-check">
					<input type="checkbox" class="form-check-input checkbox-update" id="viewNotifyPermissions" disabled{$endPointGroups['notificationPermission']}>
					<label class="form-check-label" for="viewNotifyPermissions">Email Notifications</label>
				</div>
			</div>
		</div>	
		
		<label class="fw-bold" for="createdDate">Date Created:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdDate" value="{$endPointGroups['createdDate']}" readonly>
		</div>
		<label class="fw-bold" for="createdBy">Created By:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$endPointGroups['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewepggroup").modal('show');

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