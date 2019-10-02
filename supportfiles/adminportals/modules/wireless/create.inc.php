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
	

	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewepggroup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Wireless Network</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="ssidName">Wireless Network SSID:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control" id="ssidName" name="ssidName" placeholder="" required>
		</div>
		<label class="font-weight-bold" for="ssidDescription">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control" id="ssidDescription" name="ssidDescription" placeholder="">
		</div>
	  </div>
      <div class="modal-footer">
		<a id="create" href="#" module="wireless" sub-module="create" role="button" class="btn btn-primary" data-dismiss="modal"
>Create</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'wireless'
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


if($sanitizedInput['ssidName'] != ""){
	$ipskISEDB->addWirelessNetwork($sanitizedInput['ssidName'], $sanitizedInput['ssidDescription'], $_SESSION['logonSID']);
	print $htmlbody;
}


?>
