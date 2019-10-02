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


	$portalGroupMembers = "";
	$portalEPGroupMembers = "";

	$portal = $ipskISEDB->getPortalById($sanitizedInput['id']);
	$portalGroups = $ipskISEDB->getSponsorGroupsByPortalId($sanitizedInput['id']);
	$portalEPGroups = $ipskISEDB->getEndpointGroupByPortalId($sanitizedInput['id']);
	$directoryNames = $ipskISEDB->getAuthDirectoryNames();

	if($portalGroups){
		if($portalGroups->num_rows != 0){
			while($row = $portalGroups->fetch_assoc()){
				$portalGroupMembers .= '<span class="badge badge-primary m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">'.$row['sponsorGroupName'].'</h6></span>';
			}
		}else{
			$portalGroupMembers = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$portalGroupMembers = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	if($portalEPGroups){
		if($portalEPGroups->num_rows != 0){
			while($row = $portalEPGroups->fetch_assoc()){
				$portalEPGroupMembers .= '<span class="badge badge-success m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">'.$row['groupName'].'</h6></span>';
			}
		}else{
			$portalEPGroupMembers = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$portalEPGroupMembers = '<span class="badge badge-danger m-1 p-2 font-weight-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	$portal['createAuthzButton'] = "";
	
	if($portal['portalType'] == 2 && $iseERSIntegrationAvailable){
		$portal['createAuthzButton'] = '<button type="button" module="portals" sub-module="authzprofile" id="createauthzprofile" row-id="'.$sanitizedInput['id'].'" class="btn btn-primary shadow">Create Cisco ISE Authorization Profile</button>';
	}
	
	$portalSecure = ($portal['portalSecure'] == 1) ? 'HTTPS' : 'HTTP';

	$pageTcpPortEntry = $portalSecure." (".$portal['portalTcpPort'].')';
	
	$portal['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($portal['createdBy']);
	
	$portal['createdDate'] = date($globalDateOutputFormat, strtotime($portal['createdDate']));
	
	$portalURL = strtolower($portalSecure)."://".$portal['portalHostname'].":".$portal['portalTcpPort']."/index.php?portalId=".$portal['portalId'];

$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewSponsorPortal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLongTitle">View Portal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="name">Portal Name:</label>
			<input type="text" class="form-control shadow" id="name" value="{$portal['portalName']}" readonly>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="description">Description:</label>
			<input type="text" class="form-control shadow" id="description" value="{$portal['description']}" readonly>
		</div>
		<div class="row">
			<div class="col-8">
				<div class="form-group input-group-sm font-weight-bold">
					<label class="font-weight-bold" for="hostname">Portal Hostname:</label>
					<input type="text" class="form-control shadow" id="hostname" value="{$portal['portalHostname']}" readonly>
				</div>
			</div>
			<div class="col-4">
				<div class="form-group input-group-sm font-weight-bold">
					<label class="font-weight-bold" for="hostname">App/TCP Port:</label>
					<input type="text" class="form-control shadow" id="tcpPort" value="$pageTcpPortEntry" readonly>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-8">
				<div class="form-group input-group-sm font-weight-bold">
					<label class="font-weight-bold" for="authdirectory">Authentication Directory:</label>
					<input type="text" class="form-control shadow" id="authdirectory" value="{$directoryNames[$portal['authenticationDirectory']]}" readonly>
				</div>
			</div>
			<div class="col-4">
				<div class="form-group input-group-sm font-weight-bold">
					<label class="font-weight-bold" for="hostname">Portal Type:</label>
					<input type="text" class="form-control shadow" value="{$portal['portalTypeName']}" readonly>
				</div>
			</div>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="portalid">Portal ID:</label>
			<input type="text" class="form-control shadow" id="portalid" value="{$portal['portalId']}" readonly>
		</div>
		<label class="font-weight-bold" for="portalurl">Portal URL:</label>
		<div class="input-group input-group-sm mb-3 shadow copied-popover" data-animation="true" data-container="body" data-trigger="manual" data-toggle="popover" data-placement="top" data-content="URL has been Successfully Copied!">
			<input type="text" id="portalurl" class="form-control shadow" process-value="$portalURL" value="$portalURL" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true">
			<div class="input-group-append">
				<span class="input-group-text font-weight-bold shadow" id="basic-addon1"><a id="copyPortalUrl" href="#" data-clipboard-target="#portalurl"><span id="urlfeather" data-feather="copy"></span></a></span>
			</div>
		</div>
		<label class="font-weight-bold" for="sponsorGroups">Sponsor Group Members:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="form-group font-weight-bold mb-0">		
				$portalGroupMembers
			</div>
		</div>
		<label class="font-weight-bold" for="endpointGroups">Endpoint Group Members:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="form-group font-weight-bold mb-0">		
				$portalEPGroupMembers
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label class="font-weight-bold" for="Max">Date Created:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="createdDate" value="{$portal['createdDate']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="font-weight-bold" for="createdBy">Created By:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="createdBy" value="{$portal['createdBy']}" readonly>
				</div>		
			</div>
		</div>

	  </div>
      <div class="modal-footer">
	  {$portal['createAuthzButton']}
		<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewSponsorPortal").modal();

	$(function() {	
		feather.replace()
	});
	
	var clipboard = new ClipboardJS('#copyPortalUrl');

	clipboard.on('success', function(e) {
		$('.copied-popover').popover('show');
		$('#portalurl').addClass('is-valid');
		notificationTimer = setInterval("clearNotification()", 7000);

		e.clearSelection();
	});
	
	function clearNotification(){
		$('.copied-popover').popover('hide');
		$('#portalurl').removeClass('is-valid');
		clearInterval(notificationTimer);
	}
	
	$("#createauthzprofile").click(function(event) {	
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $(this).attr('row-id')
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
		
		event.preventDefault();
	});
</script>
HTML;

print $htmlbody;
?>