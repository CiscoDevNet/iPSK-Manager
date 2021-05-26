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
 

	$sponsorGroups = "";
	$ldapDirectoryList = "";
	$hostnameList = "";
	$pageTcpPortList = "";
	$portalTypes = "";
	$idxID = 0;
	
	$sponsorGroupListing = $ipskISEDB->getSponsorGroups();
	$ldapDirectoryListing = $ipskISEDB->getLdapDirectoryListing();
	$portalHostnameList = $ipskISEDB->getHostnameList();
	$tcpPortListing = $ipskISEDB->getTcpPortList();
	$portalTypeList = $ipskISEDB->getSponsorPortalTypes();
	
	if($sponsorGroupListing){
		while($row = $sponsorGroupListing->fetch_assoc()){
			$sponsorGroups .= '<option value="'.$row['id'].'">'.$row['sponsorGroupName'].'</option>';
		}
	
		if($ldapDirectoryListing){
			while($row = $ldapDirectoryListing->fetch_assoc()){
				if($idxID == 0){
					$ldapDirectoryList .= "<option value=\"".$row['id']."\" selected>".$row['adConnectionName']."</option>";
					$idxID = 1;
				}else{
					$ldapDirectoryList .= "<option value=\"".$row['id']."\">".$row['adConnectionName']."</option>";
				}
			}
		}
		
		if($portalHostnameList){
			while($row = $portalHostnameList->fetch_assoc()){
				$hostnameList .= '<option value="'.$row['hostname'].'">'.$row['hostname'].'</option>';
			}
		}else{
			$hostnameList .= '<option value="'.$_SERVER['SERVER_ADDR'].'">'.$_SERVER['SERVER_ADDR'].'</option>';
		}
		
		if($portalTypeList){
			while($row = $portalTypeList->fetch_assoc()){
				$portalTypes .= '<option value="'.$row['id'].'">'.$row['portalTypeName'].'</option>';
			}
		}
		
		if($tcpPortListing){
			while($row = $tcpPortListing->fetch_assoc()){
				$portalSecure = ($row['portalSecure'] == 1) ? 'HTTPS' : 'HTTP';
				
				$currentProtocol = (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP';
				
				if($portalSecure == $currentProtocol){
					if($_SERVER['SERVER_PORT'] != $row['portalPort']){
						$pageTcpPortList .= '<option value="'.$row['id'].'">'.$portalSecure." (".$row['portalPort'].')</option>';
					}			
				}else{
					$pageTcpPortList .= '<option value="'.$row['id'].'">'.$portalSecure." (".$row['portalPort'].')</option>';
				}
				
				//Disabled displaying of Admin port to prevent accidental selection
				//$portalSecure = ($row['portalSecure'] == 1) ? 'HTTPS' : 'HTTP';
				//$pageTcpPortList .= '<option value="'.$row['id'].'">'.$portalSecure." (".$row['portalPort'].')</option>';
				 
			}
		}


		$htmlbody = <<<HTML
	<!-- Modal -->
	<div class="modal fade" id="addSponsorPortal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">Add Portal</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
				<form class="needs-validation" novalidate>
					<div class="form-group input-group-sm font-weight-bold">
						<label class="font-weight-bold" for="portalName">Portal Name:</label>
						<input type="text" class="form-control shadow form-validation" validation-state="required" id="portalName" value="" validation-minimum-length="1" validation-maximum-length="32">
						<small id="endpointGroupMembersBlock" class="form-text text-muted">Portal Name is Required</small>
						<div class="invalid-feedback">Please enter a Portal Name (Max: 32 Characters)</div>
					</div>
					<div class="form-group input-group-sm font-weight-bold">
						<label class="font-weight-bold" for="description">Description:</label>
						<input type="text" class="form-control shadow" id="description" value="">
					</div>
					<div class="form-group input-group-sm font-weight-bold">
						<label class="font-weight-bold" for="portalType">Portal Type:</label>
						<select class="form-control shadow form-validation" validation-state="required" id="portalType">
							$portalTypes
						</select>
						<small id="endpointGroupMembersBlock" class="form-text text-muted">Select the Portal Type you wish to create</small>
					</div>
					<div class="row">
						<div class="col-8">
							<div class="form-group input-group-sm font-weight-bold">
								<label class="font-weight-bold" for="hostname">Portal Hostname:</label>
								<select class="form-control shadow" id="hostname">
									$hostnameList
								</select>
							</div>
						</div>
						<div class="col-4">
							<div class="form-group input-group-sm font-weight-bold">
								<label class="font-weight-bold" for="tcpPort">Application Protocol (TCP Port):</label>
								<select class="form-control shadow" id="tcpPort">
									$pageTcpPortList
								</select>
							</div>
						</div>
					</div>
					<div class="form-group input-group-sm font-weight-bold">
						<label class="font-weight-bold" for="authDirectory">Authentication Directory:</label>
						<select class="form-control shadow form-validation" validation-state="required" id="authDirectory">
							<option value="0">Internal</option>
							$ldapDirectoryList
						</select>
					</div>
					<div class="form-group font-weight-bold">
						<label class="font-weight-bold" for="sponsorGroups">Sponsor Group Members:</label>	
						<select class="form-control shadow form-validation" validation-state="required" id="sponsorGroups" multiple>
							$sponsorGroups
						</select>
						<small id="endpointGroupMembersBlock" class="form-text text-muted">Minimum of 1 Group must be selected.</small>
					</div>
				</div>
				<div class="modal-footer">
					<button id="create" module="portals" sub-module="create" type="submit" class="btn btn-primary shadow">Create</button>
					<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
				</div>
				</form>
			</div>
		</div>
	</div>
	<script>
		var failure;
		
		$("#addSponsorPortal").modal();

		$(function() {	
			feather.replace()
		});
		
		$("#create").click(function(){
			event.preventDefault();
			
			failure = formFieldValidation();

			if(failure){
				return false;
			}
			
			$('.modal-backdrop').remove();
			
			$.ajax({
				url: "ajax/getmodule.php",
				
				data: {
					module: $(this).attr('module'),
					'sub-module': $(this).attr('sub-module'),
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
	}else{
		$htmlbody = <<< HTML
		<div class="modal fade" id="noPortalGroup" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header shadow alert alert-danger">
						<h5 class="modal-title font-weight-bold" id="modalLongTitle">Missing Dependencies</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p class="h6">Portal Group missing</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
		<script>
			var failure;
			
			$("#noPortalGroup").modal();

			$(function() {	
				feather.replace()
			});
		</script>
HTML;
	}

print $htmlbody;
?>