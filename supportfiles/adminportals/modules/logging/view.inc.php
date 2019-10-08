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
	


	$id = filter_var($_POST['id'],FILTER_VALIDATE_INT);

	if($id > 0){

		$logging = $ipskISEDB->getLoggingById($id);

		if($logging){	
		
			$logging['dateCreated'] = date($globalDateOutputFormat, strtotime($logging['dateCreated']));
			
			if($logging['logDataPayload'] != ""){
				$tempLogPayload = json_decode($logging['logDataPayload'], TRUE);
				$logging['logDataPayload'] = print_r($tempLogPayload,true);
			}
			
			$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewlogentry" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Log Entry</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
	  	<div class="row">
			<div class="col">
				<label class="font-weight-bold" for="dateCreated">Log Entry Date:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="dateCreated" value="{$logging['dateCreated']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="font-weight-bold" for="sessionID">Session ID:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="sessionID" value="{$logging['sessionID']}" readonly>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label class="font-weight-bold" for="functionName">Function Name:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="functionName" value="{$logging['functionName']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="font-weight-bold" for="className">Class Name:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="className" value="{$logging['className']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="font-weight-bold" for="classMethodName">Class Method Name:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="classMethodName" value="{$logging['classMethodName']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="font-weight-bold" for="lineNumber">Line Number:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="lineNumber" value="{$logging['lineNumber']}" readonly>
				</div>
			</div>
		</div>
		<label class="font-weight-bold" for="fileName">File Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="fileName" value="{$logging['fileName']}" readonly>
		</div>
		<label class="font-weight-bold" for="message">Log Entry Message:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<textarea id="message" rows="5" class="form-control shadow" readonly>{$logging['message']}</textarea>
		</div>
		<label class="font-weight-bold" for="createdBy">Log Entry Data DEBUG Payload:</label>
		
			<pre style="height: 250px;" class="border overflow-auto border-primary shadow m-0">{$logging['logDataPayload']}</pre>
		
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewlogentry").modal();

	$(function() {	
		feather.replace()
	});
</script>
HTML;
		}else{
			$htmlbody = "";
		}
		
		print $htmlbody;
	}
?>