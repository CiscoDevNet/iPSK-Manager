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
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		print "<script>window.location = \"/index.php?portalId=$portalId\";</script>";
		die();
	}
	
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
					
					//Validate the randomly generated PSK matches the one submitted to the user - Prevent Tampering
					if(password_verify($sanitizedInput['ciscoAVPairPSK'], $_SESSION['temp']['sponsoreditpsk'])){
						for($idxId = 0; $idxId < $endPointPermissions['count']; $idxId++){
							if($endPointPermissions[$idxId]['advancedPermissions'] & 1024){
								$randomPSK = "psk=".$sanitizedInput['ciscoAVPairPSK'];
					
								$endpointId = $ipskISEDB->updateEndpoint($endpoint['endpointId'],$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $_SESSION['logonSID'], $randomPSK);
							}
						}
					}
				}

				//Revoke previously generated PSK for Enduser
				unset($_SESSION['temp']);
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
		$pageValid = false;
		
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
			$pageData['endpointGroupList'] .= '<select id="associationGroup" class="form-select mt-2 mb-3 shadow" disabled>';
			
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
							$pageValid = true;
						}
					}
					
					if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 1024){
						if($_SESSION['authorizedEPGroups'][$count]['groupPermissions'] & 8){
							$endpoint['pskValue'] = substr($endpoint['pskValue'],4,strlen($endpoint['pskValue']) - 4);
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
		
		if(!$pageValid){
			$editGroup = " d-none";
		}else{
			$editGroup = '';
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
			<div class="row$editGroup">
				<div class="col m-2 shadow p-2 bg-white border border-primary">
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update" base-value="1" value="0" id="editAssociation"$endpointGroupCheck>
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
			<div class="row{$pageData['editPskValue']}">
				<div class="col m-2 shadow p-2 bg-white border border-primary">
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update" base-value="1" value="0" id="editPSK">
						<label class="form-check-label" for="editPSK">Edit Pre-Shared Key (Random)</label>
					</div>
					<label class="fw-bold" for="ciscoAVPairPSK">Pre-Shared Key:</label>
					<div class="input-group mb-3 input-group-sm fw-bold">
						<input type="password" id="ciscoAVPairPSK" class="form-control shadow" value="{$endpoint['pskValue']}" readonly disabled>
						<div class="input-group-append shadow">
							<span class="input-group-text fw-bold" id="basic-addon1"><a id="generatePSK" action="get_random_psk" href="#"><span id="passwordfeather" data-feather="refresh-cw"></span></a></span>
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
				<a id="update" href="#" module="edit" role="button" class="btn btn-primary shadow" data-bs-dismiss="modal">Update</a>
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
		
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "/edit.php?portalId=$portalId",
			
			data: {
				id: $("#id").val(),
				confirmaction: 1,
				editAssociation: $("#editAssociation").val(),
				associationGroup: $("#associationGroup").val(),
				editPSK: $("#editPSK").val(),
				ciscoAVPairPSK: $("#ciscoAVPairPSK").val(),
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
			$("#ciscoAVPairPSK").removeAttr('disabled');
			$("#ciscoAVPairPSK").attr('type','text');
			$("#editAssociation").attr('disabled','true');
		}else{
			$(this).attr('value', '0');
			$("#ciscoAVPairPSK").attr('disabled','true');
			$("#ciscoAVPairPSK").attr('type','password');
			$("#editAssociation").removeAttr('disabled');
		}
	});
	
	$("#generatePSK").on('click', function(event) {
		event.preventDefault();
		
		if($("#editPSK").val() == "1"){
			$.ajax({
				url: "/query.php?portalId=$portalId",
				data: {
					id: $("#id").val(),
					action: $(this).attr('action')
				},
				type: "POST",
				dataType: "text",
				success: function (data) {
					$("#ciscoAVPairPSK").val( data );
				}
			});
		}
	});
</script>
HTML;
	}
?>