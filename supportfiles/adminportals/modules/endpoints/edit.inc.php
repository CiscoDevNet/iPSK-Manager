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
	
	if(is_numeric($sanitizedInput['id']) && $sanitizedInput['id'] != 0 && $sanitizedInput['confirmaction'] && isset($sanitizedInput['fullName']) && isset($sanitizedInput['emailAddress']) && isset($sanitizedInput['endpointDescription']) && isset($sanitizedInput['editAssociation']) && isset($sanitizedInput['associationGroup'])){
		if($_SESSION['editAssociationEndpointId'] == $sanitizedInput['id']){
			$endpoint = $ipskISEDB->getEndpointByAssociationId($sanitizedInput['id']);
		
			if($sanitizedInput['editAssociation'] == 1){
				$endpointGroupAuthorization = $ipskISEDB->getAuthorizationTemplatesbyEPGroupId($sanitizedInput['associationGroup']);
				
				if($endpointGroupAuthorization['ciscoAVPairPSK'] == "*devicerandom*"){
					$randomPassword = $ipskISEDB->generateRandomPassword($endpointGroupAuthorization['pskLength']);
					$randomPSK = "psk=".$randomPassword;
				}elseif($endpointGroupAuthorization['ciscoAVPairPSK'] == "*userrandom*"){
					$userPsk = $ipskISEDB->getUserPreSharedKey($sanitizedInput['associationGroup'], $endpoint['createdBy']);
					if(!$userPsk){
						$randomPassword = $ipskISEDB->generateRandomPassword($endpointGroupAuthorization['pskLength']);
						$randomPSK = "psk=".$randomPassword;
					}else{
						$randomPassword = $userPsk;
						$randomPSK = "psk=".$randomPassword;
					}
				}else{
					$randomPassword = $endpointGroupAuthorization['ciscoAVPairPSK'];
					$randomPSK = "psk=".$randomPassword;
				}
				
				if($endpointGroupAuthorization['termLengthSeconds'] == 0){
					$duration = $endpointGroupAuthorization['termLengthSeconds'];
				}else{
					$duration = time() + $endpointGroupAuthorization['termLengthSeconds'];
				}

				$ipskISEDB->updateEndpoint($endpoint['endpointId'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $_SESSION['logonSID'], $randomPSK, $duration);
				$ipskISEDB->deleteEndpointAssociationbyId($sanitizedInput['id']);
				$ipskISEDB->addEndpointAssociation($endpoint['endpointId'], $endpoint['macAddress'], $sanitizedInput['associationGroup'], $_SESSION['logonSID']);
				
			}elseif($sanitizedInput['editPSK'] == 1){
				$randomPSK = "psk=".$sanitizedInput['presharedKey'];
				$endpointId = $ipskISEDB->updateEndpoint($endpoint['endpointId'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $_SESSION['logonSID'], $randomPSK);
			}else{
				$endpointId = $ipskISEDB->updateEndpoint($endpoint['endpointId'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $_SESSION['logonSID']);
			}
			
			unset($_SESSION['editAssociationEndpointId']);
		}
		
		print <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'endpoints'
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#mainContent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
</script>
HTML;
	}else{
		//Clear Variables and set to blank
		$pageData['endpointGroupList'] = "";
		$pageData['wirelessSSIDList'] = "";
		$pageData['endpointAssociationList'] = "";
		$editableForUser = false;
		
		if(!ipskLoginSessionCheck()){
			$portalId = $_GET['portalId'];
			$_SESSION = null;
			session_destroy();
			header("Location: /index.php?portalId=".$portalId);
			die();
		}
		
		$endpoint = $ipskISEDB->getEndpointByAssociationId($sanitizedInput['id']);
		
		$endpointGroups = $ipskISEDB->getEndpointGroupsAndAuthz();
		$endpoint['pskValue'] = str_replace("psk=","",$endpoint['pskValue']);
		
		$pageData['endpointGroupList'] .= '<select id="associationGroup" class="form-select mt-2 mb-3 shadow" disabled>';
		
		
		if($endpointGroups){
			$_SESSION['editAssociationEndpointId'] = $sanitizedInput['id'];
			while($row = $endpointGroups->fetch_assoc()){
				if($row["visible"] == true){
					if($row['termLengthSeconds'] == 0){
						$termLength = "No Expiry";
					}else{
						$termLength = ($row['termLengthSeconds'] / 60 / 60 / 24) . " Days";
					}
					
					if($row['ciscoAVPairPSK'] == "*userrandom*"){
						$keyType = "Randomly Chosen per User";
					}elseif($row['ciscoAVPairPSK'] == "*devicerandom*"){
						$keyType = "Randomly Chosen per Device";
					}else{
						$keyType = "Common PSK";
					}
					
					if($endpoint['epGroupId'] == $row['id']){
						$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$row['id']."\" selected>".$row['groupName']."</option>";
					}else{
						$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$row['id']."\">".$row['groupName']."</option>";
					}
				}				
			}
			$pageData['endpointGroupList'] .= "</select>";
		}
		print <<<HTML
<!-- Modal -->
<div class="modal fade" id="editEndpoint" tabindex="-1" role="dialog" aria-labelledby="editEndpointModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Endpoint Association</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
			<div class="row">
				<div class="col m-2 shadow p-2 bg-white border border-primary">
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update" base-value="1" value="0" id="editAssociation">
						<label class="form-check-label" for="editAssociation">Edit Endpoint Grouping</label>
					</div>
					<h6>Association type:</h6>
					{$pageData['endpointGroupList']}
					<div class="row">
						<div class="col">
							<p><small>
								Maximum access duration:&nbsp;<span id="duration" class="text-danger count">-</span>
							</small></p>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<p><small>
								Pre Shared Key Type:&nbsp;<span id="keyType" class="text-danger count">-</span>
							</small></p>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col m-2 shadow p-2 bg-white border border-primary">
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update" base-value="1" value="0" id="editPSK">
						<label class="form-check-label" for="editPSK">Edit Pre-Shared Key (Manual)</label>
					</div>
					<label class="fw-bold" for="psk">Pre-Shared Key:</label>
					<div class="input-group mb-3 input-group-sm fw-bold">
						<input type="password" id="presharedKey" class="form-control shadow" id="psk" value="{$endpoint['pskValue']}" disabled>
						<div class="input-group-append shadow">
							<span class="input-group-text fw-bold" id="basic-addon1"><a id="showpassword" href="#"><span id="passwordfeather" data-feather="eye"></span></a></span>
						</div>
					</div>
				</div>
			</div>
			<div class="mb-3">
				<label for="macAddress">Endpoint MAC Address</label>
				<input type="text" class="form-control mt-2 mb-3 shadow" value="{$endpoint['macAddress']}" readonly>
			</div>
			<div class="mb-3">
				<label for="endpointDescription">Endpoint Description</label>
				<input type="text" class="form-control mt-2 mb-3 user-input shadow" id="endpointDescription" value="{$endpoint['description']}">
			</div>
			<div class="mb-3">
				<label for="fullName">Full Name</label>
				<input type="text" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" id="fullName" value="{$endpoint['fullName']}">
				<div class="invalid-feedback">Please enter your Full Name</div>
			</div>
			<div class="mb-3">
				<label for="emailAddress">Email address</label>
				<input type="email" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" id="emailAddress" value="{$endpoint['emailAddress']}">
				<div class="invalid-feedback">Please enter a valid email address</div>
			</div> 
			<div class="modal-footer">
				<input type="hidden" id="id" value="{$endpoint['id']}">
				<a id="update" href="#" module="endpoints" sub-module="edit" role="button" class="btn btn-primary shadow" data-bs-dismiss="modal">Update</a>
				<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#editEndpoint").modal('show');

	$(function() {	
		feather.replace()
	});

	$("#update").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		//$('.modal-backdrop').remove();
		//$("body").removeClass('modal-open');
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
				confirmaction: 1,
				editAssociation: $("#editAssociation").val(),
				associationGroup: $("#associationGroup").val(),
				editPSK: $("#editPSK").val(),
				presharedKey: $("#presharedKey").val(),
				endpointDescription: $("#endpointDescription").val(),
				fullName: $("#fullName").val(),
				emailAddress: $("#emailAddress").val()
				
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
	});
		
	$("#associationGroup").change(function() {
		var duration = "";
		var keyType = "";
		$( "select option:selected" ).each(function() {
			duration = $(this).attr("data-term");
			keyType = $(this).attr("data-keytype");
			$( "#duration" ).html( duration );
			$( "#keyType" ).html( keyType );
		});
	});
	
	$("#associationGroup").trigger("change");
	
	$("#editAssociation").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));
			$("#associationGroup").removeAttr('disabled');
			$("#editPSK").attr('disabled','true');
		}else{
			$(this).attr('value', '0');
			$("#associationGroup").attr('disabled','true');
			$("#editPSK").removeAttr('disabled');
		}
	});
	
	$("#editPSK").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));
			$("#presharedKey").removeAttr('disabled');
			$("#editAssociation").attr('disabled','true');
		}else{
			$(this).attr('value', '0');
			$("#presharedKey").attr('disabled','true');
			$("#editAssociation").removeAttr('disabled');
		}
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
	}
?>