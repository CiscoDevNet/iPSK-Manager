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
	


	$sponsorGroups = "";
	$ldapDirectoryList = "";
	$hostnameList = "";
	$portalTypes = "";
	$pageTcpPortList = "";
	$idxID = 0;
	
	$portal = $ipskISEDB->getPortalById($sanitizedInput['id']);
	$portalGroups = $ipskISEDB->getSponsorGroupsByPortalId($sanitizedInput['id']);
	
	$sponsorGroupListing = $ipskISEDB->getSponsorGroups();
	$ldapDirectoryListing = $ipskISEDB->getLdapDirectoryListing();
	$portalHostnameList = $ipskISEDB->getHostnameList();
	$tcpPortListing = $ipskISEDB->getTcpPortList();
	$portalTypeList = $ipskISEDB->getSponsorPortalTypes();
	
	if($portalGroups){
		while($row = $portalGroups->fetch_assoc()){
			$portalGroup[$row['sponsorGroupId']] = $row['sponsorGroupName'];
		}
	}
	
	if($sponsorGroupListing){
		while($row = $sponsorGroupListing->fetch_assoc()){
			if(isset($portalGroup[$row['id']])){
				$sponsorGroups .= '<option value="'.$row['id'].'" selected>'.$row['sponsorGroupName'].'</option>';
			}else{
				$sponsorGroups .= '<option value="'.$row['id'].'">'.$row['sponsorGroupName'].'</option>';
			}
		}
	}
	
	if($ldapDirectoryListing){
		while($row = $ldapDirectoryListing->fetch_assoc()){
			if($portal['authenticationDirectory'] == $row['id']){
				$ldapDirectoryList .= "<option value=\"".$row['id']."\" selected>".$row['adConnectionName']."</option>";
			}else{
				$ldapDirectoryList .= "<option value=\"".$row['id']."\">".$row['adConnectionName']."</option>";
			}
		}
	}
	
	if($portalHostnameList){
		while($row = $portalHostnameList->fetch_assoc()){
			if($portal['portalHostname'] == $row['hostname']){
				$hostnameList .= '<option value="'.$row['hostname'].'" selected>'.$row['hostname'].'</option>';
			}else{
				$hostnameList .= '<option value="'.$row['hostname'].'">'.$row['hostname'].'</option>';
			}
		}
	}else{
		$hostnameList .= '<option value="'.$_SERVER['SERVER_ADDR'].'">'.$_SERVER['SERVER_ADDR'].'</option>';
	}
	
	if($tcpPortListing){
		while($row = $tcpPortListing->fetch_assoc()){
			$portalSecure = ($row['portalSecure'] == 1) ? 'HTTPS' : 'HTTP';
			
			$currentProtocol = (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP';
			
			if($portalSecure == $currentProtocol){
				if($_SERVER['SERVER_PORT'] != $row['portalPort']){
					if($row['portalSecure'] == $portal['portalSecure'] && $row['portalPort'] == $portal['portalTcpPort']){
						$pageTcpPortList .= '<option secured="'.$portalSecure.'" value="'.$row['id'].'" selected>'.$portalSecure." (".$row['portalPort'].')</option>';
					}else{
						$pageTcpPortList .= '<option secured="'.$portalSecure.'" value="'.$row['id'].'">'.$portalSecure." (".$row['portalPort'].')</option>';
					}
				}			
			}else{
				if($row['portalSecure'] == $portal['portalSecure'] && $row['portalPort'] == $portal['portalTcpPort']){
					$pageTcpPortList .= '<option secured="'.$portalSecure.'" value="'.$row['id'].'" selected>'.$portalSecure." (".$row['portalPort'].')</option>';
				}else{
					$pageTcpPortList .= '<option secured="'.$portalSecure.'" value="'.$row['id'].'">'.$portalSecure." (".$row['portalPort'].')</option>';
				}
			}
			
			//Disabled displaying of Admin port to prevent accidental selection
			//if($row['portalSecure'] == $portal['portalSecure'] && $row['portalPort'] == $portal['portalTcpPort']){
			//	$pageTcpPortList .= '<option secured="'.$portalSecure.'" value="'.$row['id'].'" selected>'.$portalSecure." (".$row['portalPort'].')</option>';
			//}else{
			//	$pageTcpPortList .= '<option secured="'.$portalSecure.'" value="'.$row['id'].'">'.$portalSecure." (".$row['portalPort'].')</option>';
			//}
			
		}
	}
	
	if($portalTypeList){
		while($row = $portalTypeList->fetch_assoc()){
			if($portal['portalType'] == $row['id']){
				$portalTypes .= '<option value="'.$row['id'].'" selected>'.$row['portalTypeName'].'</option>';
			}else{
				$portalTypes .= '<option value="'.$row['id'].'">'.$row['portalTypeName'].'</option>';
			}
		}
	}

$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="updateSponsorPortal" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Edit Portal</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					
				</button>
			</div>
			<div class="modal-body">
			<form class="needs-validation" novalidate>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="portalName">Portal Name:</label>
					<input type="text" class="form-control shadow form-validation" validation-state="required" id="portalName" value="{$portal['portalName']}" validation-minimum-length="1" validation-maximum-length="32">
					<small id="endpointGroupMembersBlock" class="form-text text-muted">Portal Name is Required</small>
					<div class="invalid-feedback">Please enter a Portal Name (Max: 32 Characters)</div>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="description">Description:</label>
					<input type="text" class="form-control shadow" id="description" value="{$portal['description']}">
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="portalType">Portal Type:</label>
					<select class="form-select shadow form-validation" validation-state="required" id="portalType">
						$portalTypes
					</select>
					<small id="endpointGroupMembersBlock" class="form-text text-muted">Select the Portal Type you wish to create</small>
				</div>
				<div class="row">
					<div class="col-8">
						<div class="mb-3 input-group-sm fw-bold">
							<label class="fw-bold" for="hostname">Portal Hostname:</label>
							<select class="form-select shadow" id="hostname">
								$hostnameList
							</select>
						</div>
					</div>
					<div class="col-4">
						<div class="mb-3 input-group-sm fw-bold">
							<label class="fw-bold" for="tcpPort">Application Protocol (TCP Port):</label>
							<select class="form-select shadow" id="tcpPort">
								$pageTcpPortList
							</select>
						</div>
					</div>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="authDirectory">Authentication Directory:</label>
					<select class="form-select shadow form-validation" validation-state="required" id="authDirectory">
						<option value="0">Internal</option>
						$ldapDirectoryList
					</select>
				</div>
				<div class="mb-3 fw-bold">
					<label class="fw-bold" for="sponsorGroups">Sponsor Group Members:</label>	
					<select class="form-select shadow form-validation" validation-state="required" id="sponsorGroups" multiple>
						$sponsorGroups
					</select>
					<small id="endpointGroupMembersBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" id="id" value="{$portal['id']}">
				<button id="update" module="portals" sub-module="update" type="submit" class="btn btn-primary shadow">Update</button>
				<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
			</div>
			</form>
		</div>
	</div>
</div>
<script>
	var failure;
	
	$("#updateSponsorPortal").modal('show');

	$(function() {	
		feather.replace()
	});
	
	$("#update").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('updateSponsorPortal'));
			modal.hide();
		}
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
				portalName: $("#portalName").val(),
				description: $("#description").val(),
				hostname: $("#hostname").val(),
				tcpPort: $("#tcpPort").val(),
				authDirectory: $("#authDirectory").val(),
				sponsorPortalType: $("#portalType").val(),
				sponsorGroups: $("#sponsorGroups").val()
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
	
	$(".checkbox-update").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));		
		}else{
			$(this).attr('value', '0');
		}
		
	});
	
	$("#sponsorGroupAuthType").change(function(){
		event.preventDefault();
		
		$.ajax({
			url: "ajax/getdata.php",
			data: {
				'data-command': $(this).attr('data-command'),
				'data-set': $(this).attr('data-set'),
				'id': $(this).find('option:selected').val()
			},
			type: "POST",
			dataType: "json",
			success: function (data) {
				$("#authorizationGroups").find("option").remove(),
				$.each(data, function(index, element) {
					temp = $('<option>', {value: index}),
					$("#authorizationGroups").append(temp.html(element));
					});
			}
		});
	});
	
</script>
HTML;

print $htmlbody;
?>