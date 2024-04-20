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

		$logging = $ipskISEDB->getLoggingById($id);

		if($logging){	
		
			$logging['dateCreated'] = date($globalDateOutputFormat, strtotime($logging['dateCreated']));
			
			if($logging['logDataPayload'] != ""){
				$tempLogPayload = json_decode($logging['logDataPayload'], TRUE);
				$logging['logDataPayload'] = print_r($tempLogPayload,true);
			}
			
			$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewlogentry" tabindex="-1" role="dialog" aria-labelledby="viewlogentryModal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View Log Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
	  	<div class="row">
			<div class="col">
				<label class="fw-bold" for="dateCreated">Log Entry Date:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="dateCreated" value="{$logging['dateCreated']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="sessionID">Session ID:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="sessionID" value="{$logging['sessionID']}" readonly>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<label class="fw-bold" for="functionName">Function Name:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="functionName" value="{$logging['functionName']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="className">Class Name:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="className" value="{$logging['className']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="classMethodName">Class Method Name:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="classMethodName" value="{$logging['classMethodName']}" readonly>
				</div>
			</div>
			<div class="col">
				<label class="fw-bold" for="lineNumber">Line Number:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="lineNumber" value="{$logging['lineNumber']}" readonly>
				</div>
			</div>
		</div>
		<label class="fw-bold" for="fileName">File Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="fileName" value="{$logging['fileName']}" readonly>
		</div>
		<label class="fw-bold" for="message">Log Entry Message:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<textarea id="message" rows="5" class="form-control shadow" readonly>{$logging['message']}</textarea>
		</div>
		<label class="fw-bold" for="createdBy">Log Entry Data DEBUG Payload:</label>
		
			<pre style="height: 250px;" class="border overflow-auto border-primary shadow m-0">{$logging['logDataPayload']}</pre>
		
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewlogentry").modal('show');

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