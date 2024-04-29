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
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="updatewireless" tabindex="-1" role="dialog" aria-labelledby="updatewirelessModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Wireless Network</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="ssidName">Wireless Network SSID:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" id="ssidName" name="ssidName" value="{$wirelessNetwork['ssidName']}" required>
			<div class="invalid-feedback">Please enter a valid SSID Name</div>
		</div>
		<label class="fw-bold" for="ssidDescription">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="ssidDescription" name="ssidDescription" value="{$wirelessNetwork['ssidDescription']}">
		</div>
	  </div>
      <div class="modal-footer">
		<input type="hidden" id="id" value="{$wirelessNetwork['id']}">
		<a id="update" href="#" module="wireless" sub-module="update" role="button" class="btn btn-primary shadow">Update</a>
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#updatewireless").modal('show');
	
	$("#update").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('updatewireless'));
			modal.hide();
		}

		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
				ssidName: $("#ssidName").val(),
				ssidDescription: $("#ssidDescription").val()
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
?>