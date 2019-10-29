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
				$endPointPermissions = $ipskISEDB->getEndPointAssociationPermissions($sanitizedInput['id'],$_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id']);
				
				if(isset($endPointPermissions['count'])){
					for($idxId = 0; $idxId < $endPointPermissions['count']; $idxId++){
						if($endPointPermissions[$idxId]['advancedPermissions'] & 1024){
							$randomPSK = "psk=".$sanitizedInput['presharedKey'];
				
							$endpointId = $ipskISEDB->updateEndpoint($endpoint['endpointId'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $_SESSION['logonSID'], $randomPSK);
						}
					}
				}
			}else{
				$endpointId = $ipskISEDB->updateEndpoint($endpoint['endpointId'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $_SESSION['logonSID']);
			}
			
			unset($_SESSION['editAssociationEndpointId']);
		}
		
		print <<<HTML
<script>
	window.location = "/manage.php?portalId=$portalId";
</script>
HTML;
	}else{
		//Clear Variables and set to blank
		$pageData['endpointGroupList'] = "";
		$pageData['wirelessSSIDList'] = "";
		$pageData['endpointAssociationList'] = "";
		$editableForUser = false;
		$viewPSKPermission = false;
		
		if(!ipskLoginSessionCheck()){
			$portalId = $_GET['portalId'];
			$_SESSION = null;
			session_destroy();
			header("Location: /index.php?portalId=".$portalId);
			die();
		}
		
		$endpoint = $ipskISEDB->getEndpointByAssociationId($sanitizedInput['id']);

		//Check if User is allowed to edit Endpoint
		for($count = 0; $count < $_SESSION['authorizedEPGroups']['count']; $count++){
			if($endpoint['epGroupId'] == $_SESSION['authorizedEPGroups'][$count]['endpointGroupId']){
				if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 256){
					$_SESSION['editAssociationEndpointId'] = $sanitizedInput['id'];
					$editableForUser = true;
				}
			}
		}
		
		//Bail out if not allowed to edit endpoint
		if($editableForUser == false){
			die();
		}
		
		if(is_array($_SESSION['authorizedEPGroups'])){
			$pageData['endpointGroupList'] .= '<select id="associationGroup" class="form-control mt-2 mb-3 shadow" disabled>';
			
			for($count = 0; $count < $_SESSION['authorizedEPGroups']['count']; $count++){
				if(!isset($trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']])){
					//Check if User is authorized for Create on EndPoint Group
					if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 512){
						if($ipskISEDB->getUserEndpointCount($_SESSION['authorizedEPGroups'][$count]['endpointGroupId'], $_SESSION['logonSID']) < $_SESSION['authorizedEPGroups'][$count]['maxDevices']){
							if($_SESSION['authorizedEPGroups'][$count]['termLengthSeconds'] == 0){
								$termLength = "No Expiry";
							}else{
								$termLength = ($_SESSION['authorizedEPGroups'][$count]['termLengthSeconds'] / 60 / 60 / 24) . " Days";
							}
							
							if($_SESSION['authorizedEPGroups'][$count]['ciscoAVPairPSK'] == "*userrandom*"){
								$keyType = "Randomly Chosen per User";
							}elseif($_SESSION['authorizedEPGroups'][$count]['ciscoAVPairPSK'] == "*devicerandom*"){
								$keyType = "Randomly Chosen per Device";
							}else{
								$keyType = "Common PSK";
							}
							
							if($endpoint['epGroupId'] == $_SESSION['authorizedEPGroups'][$count]['endpointGroupId']){
								$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\" selected>".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
							}else{
								$pageData['endpointGroupList'] .= "<option data-keytype=\"$keyType\" data-term=\"$termLength\" value=\"".$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']."\">".$_SESSION['authorizedEPGroups'][$count]['groupName']."</option>";
							}					
							
							$trackSeenObjects[$_SESSION['authorizedEPGroups'][$count]['endpointGroupId']] = true;
						}
					}
					
					if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 1024){
						if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 8){
							$endpoint['pskValue'] = str_replace("psk=","",$endpoint['pskValue']);
						}else{
							$endpoint['pskValue'] = '';
						}
						
						$pageData['editPskValue'] = "";
						
					}else{
						$pageData['editPskValue'] = " d-none";
					}
				}
			}
			
			$pageData['endpointGroupList'] .= "</select>";
			
			if($_SESSION['portalAuthorization']['create'] == false){
				$endpointGroupCheck = " disabled";
			}else{
				$endpointGroupCheck = "";
			}
			
			unset($trackSeenObjects);
		}
	
		print <<<HTML
<!-- Modal -->
<div class="modal fade" id="editEndpoint" tabindex="-1" role="dialog" aria-labelledby="editEndpointModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Endpoint Association</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<div class="row">
				<div class="col m-2 shadow p-2 bg-white border border-primary">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input checkbox-update" base-value="1" value="0" id="editAssociation"$endpointGroupCheck>
						<label class="custom-control-label" for="editAssociation">Edit Endpoint Grouping</label>
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
			<div class="row{$pageData['editPskValue']}">
				<div class="col m-2 shadow p-2 bg-white border border-primary">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input checkbox-update" base-value="1" value="0" id="editPSK">
						<label class="custom-control-label" for="editPSK">Edit Pre-Shared Key (Manual)</label>
					</div>
					<label class="font-weight-bold" for="presharedKey">Pre-Shared Key:</label>
					<div class="input-group form-group input-group-sm font-weight-bold">
						<input type="password" id="presharedKey" class="form-control shadow" value="{$endpoint['pskValue']}" disabled>
						<div class="input-group-append shadow">
							<span class="input-group-text font-weight-bold" id="basic-addon1"><a id="showpassword" href="#"><span id="passwordfeather" data-feather="eye"></span></a></span>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label for="macAddress">Endpoint MAC Address</label>
				<input type="text" class="form-control mt-2 mb-3 shadow" value="{$endpoint['macAddress']}" readonly>
			</div>
			<div class="form-group">
				<label for="endpointDescription">Endpoint Description</label>
				<input type="text" class="form-control mt-2 mb-3 user-input shadow" id="endpointDescription" value="{$endpoint['description']}">
			</div>
			<div class="form-group">
				<label for="fullName">Full Name</label>
				<input type="text" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" id="fullName" value="{$endpoint['fullName']}">
				<div class="invalid-feedback">Please enter your Full Name</div>
			</div>
			<div class="form-group">
				<label for="emailAddress">Email address</label>
				<input type="email" class="form-control mt-2 mb-3 user-input shadow form-validation" validation-state="required" id="emailAddress" value="{$endpoint['emailAddress']}">
				<div class="invalid-feedback">Please enter a valid email address</div>
			</div> 
			<div class="modal-footer">
				<input type="hidden" id="id" value="{$endpoint['id']}">
				<a id="update" href="#" module="edit" role="button" class="btn btn-primary shadow" data-dismiss="modal">Update</a>
				<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#editEndpoint").modal();

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
			url: "/edit.php?portalId=$portalId",
			
			data: {
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