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
		print "<script>window.location = \"index.php?portalId=$portalId\";</script>";
		die();
	}
	
	$viewPSKPermission = false;

	$endPointAssociation = $ipskISEDB->getEndPointAssociationById($sanitizedInput['id']);
	
	if($_SESSION['portalAuthorization']['viewallPSK'] == true){
		$endPointAssociation['pskValue'] = '<input type="password" id="presharedKey" class="form-control shadow" id="psk" value="'.str_replace("psk=","",$endPointAssociation['pskValue']).'" readonly>';
	}else{
		$endPointPermissions = $ipskISEDB->getEndPointAssociationPermissions($sanitizedInput['id'],$_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id']);
		
		if(isset($endPointPermissions['count'])){
			for($idxId = 0; $idxId < $endPointPermissions['count']; $idxId++){
				if($endPointPermissions[$idxId]['viewPermissions'] & 8){
					if($endPointPermissions[$idxId]['viewPermissions'] & 2){
						for($groupCount = 0; $groupCount < $_SESSION['authorizedEPGroups']['count']; $groupCount++){
							if($endPointAssociation['epGroupId'] == $_SESSION['authorizedEPGroups'][$groupCount]['endpointGroupId']){
								if($_SESSION['authorizedEPGroups'][$groupCount]['viewPermissions'] & 2){
									$viewPSKPermission = true;
									break;
								}
							}
						}
					}elseif($endPointPermissions[$idxId]['viewPermissions'] & 1){
						if($endPointAssociation['createdBy'] == $_SESSION['logonSID']){
							$viewPSKPermission = true;
							break;
						}
					}
				}
			}
			
			if($viewPSKPermission){
				$endPointAssociation['pskValue'] = '<input type="password" id="presharedKey" class="form-control shadow" id="psk" value="'.str_replace("psk=","",$endPointAssociation['pskValue']).'" readonly>';
			}else{
				$endPointAssociation['pskValue'] = '<input type="text" id="presharedKey" class="form-control shadow text-danger" id="psk" value="NO VIEW PERMISSION" readonly>';
			}
		}else{
			$endPointAssociation['pskValue'] = '<input type="text" id="presharedKey" class="form-control shadow text-danger" id="psk" value="NO VIEW PERMISSION" readonly>';
		}
	}
	
	$endPointAssociation['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($endPointAssociation['createdBy']);
	
	$endPointAssociation['epCreatedDate'] = date($globalDateOutputFormat, strtotime($endPointAssociation['epCreatedDate']));
	
	$endPointAssociation['createdDate'] = date($globalDateOutputFormat, strtotime($endPointAssociation['createdDate']));
	
	$endPointAssociation['lastAccessed'] = date($globalDateOutputFormat, strtotime($endPointAssociation['lastAccessed']));
	
	if($endPointAssociation['expirationDate'] == 0){
		$endPointAssociation['expirationDate'] = "Never";
	}elseif($endPointAssociation['expirationDate'] < time()){
		$endPointAssociation['expirationDate'] = 'Expired';
	}else{
		$endPointAssociation['expirationDate'] = date($globalDateOutputFormat,$endPointAssociation['expirationDate']);
	}
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewendpoint" tabindex="-1" role="dialog" aria-labelledby="viewendpointModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Endpoint Association</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="macAddress">Endpoint MAC Address</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="macAddress" value="{$endPointAssociation['macAddress']}" readonly>
		</div>
		<label class="fw-bold" for="fullName">Full Name</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="fullName" value="{$endPointAssociation['fullName']}" readonly>
		</div>
		<label class="fw-bold" for="description">Description</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="description" value="{$endPointAssociation['description']}" readonly>
		</div>
		<label class="fw-bold" for="emailAddress">Email Address</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="emailAddress" value="{$endPointAssociation['emailAddress']}" readonly>
		</div>
		<label class="fw-bold" for="epGroupName">iPSK Endpoint Grouping</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="epGroupName" value="{$endPointAssociation['epGroupName']}" readonly>
		</div>
		<div class="row">
			<div class="col">
				<label class="fw-bold" for="expirationDate">Expiration Date:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="expirationDate" value="{$endPointAssociation['expirationDate']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="lastAccessed">Last Accessed Date:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="lastAccessed" value="{$endPointAssociation['lastAccessed']}" readonly>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label class="fw-bold" for="epCreatedDate">Endpoint Creation Date:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="epCreatedDate" value="{$endPointAssociation['epCreatedDate']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="createdDate">Association Creation Date:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="createdDate" value="{$endPointAssociation['createdDate']}" readonly>
				</div>
			</div>
		</div>
		<label class="fw-bold" for="psk">Pre-Shared Key:</label>
		<div class="input-group mb-3 input-group-sm fw-bold">
			{$endPointAssociation['pskValue']}
			<div class="input-group-append shadow">
				<span class="input-group-text fw-bold" id="basic-addon1"><a id="showpassword" href="#"><span id="passwordfeather" data-feather="eye"></span></a></span>
			</div>
		</div>
		<label class="fw-bold" for="createdBy">Created By:</label>
		<div class="mb-3 input-group-sm fw-bold shadow">
			<input type="text" class="form-control shadow" id="createdBy" value="{$endPointAssociation['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewendpoint").modal('show');

	$(function() {	
		feather.replace()
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

print $htmlbody;
?>