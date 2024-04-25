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


	$portalGroupMembers = "";
	$portalEPGroupMembers = "";

	$portal = $ipskISEDB->getPortalById($sanitizedInput['id']);
	$portalGroups = $ipskISEDB->getSponsorGroupsByPortalId($sanitizedInput['id']);
	$portalEPGroups = $ipskISEDB->getEndpointGroupByPortalId($sanitizedInput['id']);
	$directoryNames = $ipskISEDB->getAuthDirectoryNames();

	if($portalGroups){
		if($portalGroups->num_rows != 0){
			while($row = $portalGroups->fetch_assoc()){
				$portalGroupMembers .= '<span class="badge text-bg-primary m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">'.$row['sponsorGroupName'].'</h6></span>';
			}
		}else{
			$portalGroupMembers = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$portalGroupMembers = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	if($portalEPGroups){
		if($portalEPGroups->num_rows != 0){
			while($row = $portalEPGroups->fetch_assoc()){
				$portalEPGroupMembers .= '<span class="badge text-bg-success m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">'.$row['groupName'].'</h6></span>';
			}
		}else{
			$portalEPGroupMembers = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
		}
	}else{
		$portalEPGroupMembers = '<span class="badge text-bg-danger m-1 p-2 fw-bold shadow text-large"><h6 class="mb-0">(None)</h6></span>';
	}
	
	$portal['createAuthzButton'] = "";
	
	if($portal['portalType'] == 2 && $iseERSIntegrationAvailable){
		$portal['createAuthzButton'] = '<button type="button" module="portals" sub-module="authzprofile" id="createauthzprofile" row-id="'.$sanitizedInput['id'].'" class="btn btn-primary shadow" data-bs-dismiss="modal">Create Cisco ISE Authorization Profile</button>';
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<div class="mb-3 input-group-sm fw-bold">
			<label class="fw-bold" for="name">Portal Name:</label>
			<input type="text" class="form-control shadow" id="name" value="{$portal['portalName']}" readonly>
		</div>
		<div class="mb-3 input-group-sm fw-bold">
			<label class="fw-bold" for="description">Description:</label>
			<input type="text" class="form-control shadow" id="description" value="{$portal['description']}" readonly>
		</div>
		<div class="row">
			<div class="col-8">
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="hostname">Portal Hostname:</label>
					<input type="text" class="form-control shadow" id="hostname" value="{$portal['portalHostname']}" readonly>
				</div>
			</div>
			<div class="col-4">
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="hostname">App/TCP Port:</label>
					<input type="text" class="form-control shadow" id="tcpPort" value="$pageTcpPortEntry" readonly>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-8">
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="authdirectory">Authentication Directory:</label>
					<input type="text" class="form-control shadow" id="authdirectory" value="{$directoryNames[$portal['authenticationDirectory']]}" readonly>
				</div>
			</div>
			<div class="col-4">
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="hostname">Portal Type:</label>
					<input type="text" class="form-control shadow" value="{$portal['portalTypeName']}" readonly>
				</div>
			</div>
		</div>
		<div class="mb-3 input-group-sm fw-bold">
			<label class="fw-bold" for="portalid">Portal ID:</label>
			<input type="text" class="form-control shadow" id="portalid" value="{$portal['portalId']}" readonly>
		</div>
		<label class="fw-bold" for="portalurl">Portal URL:</label>
		<div class="input-group input-group-sm mb-3 shadow copied-popover" data-bs-animation="true" data-bs-container="body" data-bs-trigger="manual" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="URL has been Successfully Copied!">
			<input type="text" id="portalurl" class="form-control shadow" process-value="$portalURL" value="$portalURL" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true">
			<div class="input-group-append">
				<span class="input-group-text fw-bold shadow" id="basic-addon1"><a id="copyPortalUrl" href="#" data-clipboard-target="#portalurl"><span id="urlfeather" data-feather="copy"></span></a></span>
			</div>
		</div>
		<label class="fw-bold" for="sponsorGroups">Sponsor Group Members:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="mb-3 fw-bold mb-0">		
				$portalGroupMembers
			</div>
		</div>
		<label class="fw-bold" for="endpointGroups">Endpoint Group Members:</label>
		<div class="module-box shadow border border-primary p-2">
			<div class="mb-3 fw-bold mb-0">		
				$portalEPGroupMembers
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label class="fw-bold" for="Max">Date Created:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="createdDate" value="{$portal['createdDate']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="createdBy">Created By:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="createdBy" value="{$portal['createdBy']}" readonly>
				</div>		
			</div>
		</div>

	  </div>
      <div class="modal-footer">
	  {$portal['createAuthzButton']}
		<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewSponsorPortal").modal('show');

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
		//$('.modal-backdrop').remove();
		//$("body").removeStyle();
		//$("body").removeClass('modal-open');
		
		
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