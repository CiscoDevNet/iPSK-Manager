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
	


	$id = filter_var($_POST['id'],FILTER_VALIDATE_INT);

	if($id > 0){

		$wirelessNetwork = $ipskISEDB->getWirelessNetworkById($id);

		if($wirelessNetwork){
			
			$wirelessNetwork['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($wirelessNetwork['createdBy']);

			$wirelessNetwork['createdDate'] = date($globalDateOutputFormat, strtotime($wirelessNetwork['createdDate']));
	
			$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewepggroup" tabindex="-1" role="dialog" aria-labelledby="viewepggroupModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Wireless Network</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="ssidName">Wireless Network SSID:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="ssidName" value="{$wirelessNetwork['ssidName']}" readonly>
		</div>
		<label class="fw-bold" for="ssidDescription">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="ssidDescription" value="{$wirelessNetwork['ssidDescription']}" readonly>
		</div>
		<label class="fw-bold" for="createdBy">Date Created:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$wirelessNetwork['createdDate']}" readonly>
		</div>
		<label class="fw-bold" for="createdBy">Created By:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$wirelessNetwork['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewepggroup").modal('show');

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
		}else{
			$htmlbody = "";
		}
		
		print $htmlbody;
	}
?>