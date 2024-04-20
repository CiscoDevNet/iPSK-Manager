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

	
	if($sanitizedInput['id'] && $sanitizedInput['id'] != "" && $sanitizedInput['authzProfileName'] != ""){
		
		if($iseERSIntegrationAvailable){
			if(!$ipskISEERS->check_ifAuthZProfileExists($sanitizedInput['authzProfileName'])){
				$result = $ipskISEERS->createCaptivePortalAuthzProfile($sanitizedInput['authzProfileName'], $sanitizedInput['description'], $ipskISEDB->getPortalURL($sanitizedInput['id']));
				
				if($result){
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:SUCCESS;ACTION:PORTAL-AUTHZ-PROFILE;PROFILE-NAME:".$sanitizedInput['authzProfileName'].";HOSTNAME:".$_SERVER['SERVER_NAME'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
					$logMessage = "REQUEST:FAILURE[failed_to_create_authz_profile];ACTION:PORTAL-AUTHZ-PROFILE;PROFILE-NAME:".$sanitizedInput['authzProfileName'].";HOSTNAME:".$_SERVER['SERVER_NAME'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:FAILURE[ise_ers_integration_not_available];ACTION:PORTAL-AUTHZ-PROFILE;PROFILE-NAME:".$sanitizedInput['authzProfileName'].";HOSTNAME:".$_SERVER['SERVER_NAME'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		}
		
		
				$htmlbody = <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'portals'
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

		print $htmlbody;
		die();
		
		
	}else{
		$portal = $ipskISEDB->getPortalById($sanitizedInput['id']);
		
		if($portal['portalType'] == 2){
			$portalURL = $ipskISEDB->getPortalURL($sanitizedInput['id']);

			$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="createISEAuthzProfile" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Create Cisco ISE Authorization Profile</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					
				</button>
			</div>
			<div class="modal-body">
			<form class="needs-validation" novalidate>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="portalName">Portal Name:</label>
					<input type="text" class="form-control shadow" id="portalName" value="{$portal['portalName']}" readonly>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="portalUrl">Portal URL:</label>
					<input type="text" class="form-control shadow" id="portalUrl" value="$portalURL" readonly>
				</div>
				<label class="fw-bold" for="authorizationProfileName">Cisco ISE Authorization Profile Name:</label>
				<div class="input-group input-group-sm mb-3">
					<input type="text" id="authorizationProfileName" class="form-control shadow form-validation" validation-state="required" validation-minimum-length="5" validation-maximum-length="20">
					<div class="input-group-append shadow">
						<span class="input-group-text fw-bold" id="basic-addon1"><a id="checkauthzprofile" data-command="validate" data-set="authzprofile" href="#">Validate</a></span>
					</div>
					<div class="invalid-feedback">Please enter a Authorization Profile Name (Minimum length of 5 characters)</div>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="description">Cisco ISE Authorization Profile Description:</label>
					<input type="text" class="form-control shadow" id="description">
				</div>
			</div>
			<div class="modal-footer">
				<button id="addauthzprofile" module="portals" sub-module="authzprofile" row-id="{$sanitizedInput['id']}" type="submit" class="btn btn-primary shadow" data-bs-dismiss="modal">Create Authorization Profile</button>
				<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
			</div>
			</form>
		</div>
	</div>
</div>
<script>
	var failure;
	
	$("#createISEAuthzProfile").modal('show');

	$(function() {	
		feather.replace()
	});
	
	$("#checkauthzprofile").click(function(event) {
		if($("#authorizationProfileName").val() != ""){
			$.ajax({
				url: "ajax/getdata.php",
				
				data: {
					'data-command': $(this).attr('data-command'),
					'data-set': $(this).attr('data-set'),
					'authzProfileName': $("#authorizationProfileName").val()
				},
				type: "POST",
				dataType: "html",
				success: function (data) {
					if(data == "not_exists"){
						$("#authorizationProfileName").removeClass('is-invalid');
						$("#authorizationProfileName").addClass('is-valid');
					}else{
						$("#authorizationProfileName").addClass('is-invalid');
						$("#authorizationProfileName").removeClass('is-valid');
					}
				}
			});
		}else{
			$("#authorizationProfileName").addClass('is-invalid');
						$("#authorizationProfileName").removeClass('is-valid');
		}
		
		event.preventDefault();
	});
	
	$("#addauthzprofile").click(function(){
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
				id: $(this).attr('row-id'),
				confirmaction: 1,
				'authzProfileName': $("#authorizationProfileName").val(),
				'description': $("#description").val()
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
	

	
</script>
HTML;
			print $htmlbody;
		}
	}
?>