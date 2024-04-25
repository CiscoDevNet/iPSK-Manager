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
<div class="modal fade" id="viewAuthz" tabindex="-1" role="dialog" aria-labelledby="viewAuthzModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Authorization Template</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="authzPolicyName">Authorization Template Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="authzPolicyName" value="{$authorizationTemplate['authzPolicyName']}" readonly>
		</div>
		<label class="fw-bold" for="authzPolicyDescription">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="authzPolicyDescription" value="{$authorizationTemplate['authzPolicyDescription']}" readonly>
		</div>
		<label class="fw-bold" for="termLengthSeconds">Access Term Length:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="termLengthSeconds" value="{$authorizationTemplate['termLengthSeconds']}" readonly>
		</div>
		<label class="fw-bold" for="pskLength">Pre-Shared Key Length:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="pskLength" value="{$authorizationTemplate['pskLength']}" readonly>
			<div class="invalid-feedback">Please enter a PSK length greater than 8 and less than 64</div>
		</div>
		<label class="fw-bold" for="ciscoAVPairPSKMode">Pre-Shared Key:</label>
		<div class="input-group input-group-sm mb-3 shadow">
			<div class="input-group-prepend shadow">
				<span class="input-group-text fw-bold" id="basic-addon1">{$authorizationTemplate['ciscoAVPairPSKMode']}</span>
			</div>
			<input type="password" id="ciscoAVPairPSKMode" class="form-control shadow" value="{$authorizationTemplate['ciscoAVPairPSK']}" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true" readonly>
			<div class="input-group-append shadow">
				<span class="input-group-text fw-bold shadow" id="basic-addon1"><a id="showpassword" href="#"><span id="passwordfeather" data-feather="eye"></span></a></span>
			</div>
		</div>
		<label class="fw-bold" for="createdDate">Date Created:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdDate" value="{$authorizationTemplate['createdDate']}" readonly>
		</div>
		<label class="fw-bold" for="createdBy">Created By:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$authorizationTemplate['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewAuthz").modal('show');

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