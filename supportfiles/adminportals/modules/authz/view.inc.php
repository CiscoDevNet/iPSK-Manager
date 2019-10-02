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
	


	$authorizationTemplate = $ipskISEDB->getAuthorizationTemplatesById($sanitizedInput['id']);
	
	if($authorizationTemplate['ciscoAVPairPSK'] == "*userrandom*"){
		$authorizationTemplate['ciscoAVPairPSK'] = "Randomly Chosen per User";
	}elseif($authorizationTemplate['ciscoAVPairPSK'] == "*devicerandom*"){
		$authorizationTemplate['ciscoAVPairPSK'] = "Randomly Chosen per Device";
	}
	
	$authorizationTemplate['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($authorizationTemplate['createdBy']);
	
	$authorizationTemplate['createdDate'] = date($globalDateOutputFormat, strtotime($authorizationTemplate['createdDate']));
	
	$authorizationTemplate['ciscoAVPairPSKMode'] = strtoupper($authorizationTemplate['ciscoAVPairPSKMode']);
	
	if($authorizationTemplate['termLengthSeconds'] == 0){
		$authorizationTemplate['termLengthSeconds'] = "No Expiration";
	}else{
		if(($authorizationTemplate['termLengthSeconds'] / 31536000) >= 1){
			$authorizationTemplate['termLengthSeconds'] = ($authorizationTemplate['termLengthSeconds'] / 31536000)." Years";
		}elseif(($authorizationTemplate['termLengthSeconds'] / 2592000) >= 1){
			$authorizationTemplate['termLengthSeconds'] = ($authorizationTemplate['termLengthSeconds'] / 2592000)." Months";
		}elseif(($authorizationTemplate['termLengthSeconds'] / 604800) >= 1){
			$authorizationTemplate['termLengthSeconds'] = ($authorizationTemplate['termLengthSeconds'] / 604800)." Weeks";
		}elseif(($authorizationTemplate['termLengthSeconds'] / 86400) >= 1){
			$authorizationTemplate['termLengthSeconds'] = ($authorizationTemplate['termLengthSeconds'] / 86400)." Days";
		}else{
			$authorizationTemplate['termLengthSeconds'] = ($authorizationTemplate['termLengthSeconds'] / 86400)." Days";
		}
	}
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewAuthz" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Authorization Template</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="authzPolicyName">Authorization Template Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="authzPolicyName" value="{$authorizationTemplate['authzPolicyName']}" readonly>
		</div>
		<label class="font-weight-bold" for="authzPolicyDescription">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="authzPolicyDescription" value="{$authorizationTemplate['authzPolicyDescription']}" readonly>
		</div>
		<label class="font-weight-bold" for="termLengthSeconds">Access Term Length:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="termLengthSeconds" value="{$authorizationTemplate['termLengthSeconds']}" readonly>
		</div>
		<label class="font-weight-bold" for="pskLength">Pre-Shared Key Length:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="pskLength" value="{$authorizationTemplate['pskLength']}" readonly>
			<div class="invalid-feedback">Please enter a PSK length greater than 8 and less than 64</div>
		</div>
		<label class="font-weight-bold" for="ciscoAVPairPSKMode">Pre-Shared Key:</label>
		<div class="input-group input-group-sm mb-3 shadow">
			<div class="input-group-prepend shadow">
				<span class="input-group-text font-weight-bold" id="basic-addon1">{$authorizationTemplate['ciscoAVPairPSKMode']}</span>
			</div>
			<input type="password" id="ciscoAVPairPSKMode" class="form-control shadow" value="{$authorizationTemplate['ciscoAVPairPSK']}" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true" readonly>
			<div class="input-group-append shadow">
				<span class="input-group-text font-weight-bold shadow" id="basic-addon1"><a id="showpassword" href="#"><span id="passwordfeather" data-feather="eye"></span></a></span>
			</div>
		</div>
		<label class="font-weight-bold" for="createdDate">Date Created:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdDate" value="{$authorizationTemplate['createdDate']}" readonly>
		</div>
		<label class="font-weight-bold" for="createdBy">Created By:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$authorizationTemplate['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewAuthz").modal();

	$(function() {	
		feather.replace()
	});
	
	$("#showpassword").on('click', function(event) {
		event.preventDefault();
		if($("#ciscoAVPairPSKMode").attr('type') == "text"){
			$("#ciscoAVPairPSKMode").attr('type', 'password');
			$("#passwordfeather").attr('data-feather','eye');
			feather.replace();
		}else if($("#ciscoAVPairPSKMode").attr('type') == "password"){
			$("#ciscoAVPairPSKMode").attr('type', 'text');
			$("#passwordfeather").attr('data-feather','eye-off');
			feather.replace();
		}
	});
</script>
HTML;

print $htmlbody;
?>