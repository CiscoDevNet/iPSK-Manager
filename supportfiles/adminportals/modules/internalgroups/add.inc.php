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
<div class="modal fade" id="addInternalGroup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Internal Group</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="groupName">Group Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" id="groupName" name="groupName" placeholder="" required>
			<div class="invalid-feedback">Please enter a valid Group Name</div>
		</div>
		<label class="font-weight-bold" for="description">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="description" name="ssidDescription" placeholder="">
		</div>
		<label class="font-weight-bold" for="groupType">Group Type:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<select id="groupType" class="form-control mt-2 mb-3 shadow">
				<option value="0">Internal</option>
				<option value="1">External</option>
			</select>
		</div>
		<div id="dnBlock" class="row d-none">
			<div class="col">
				<label class="font-weight-bold" for="groupDn">External Group Distinguished Name:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" validation-state="required"  class="form-control shadow" id="groupDn">
					<div class="invalid-feedback">Please enter a Distinguished Name</div>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" name="permission" base-value="1" value="0" id="permission">
					<label class="custom-control-label" for="permission">Admin Portal Access</label>
				</div>
			</div>
		</div>
	  </div>
      <div class="modal-footer">
		<a id="create" href="#" module="internalgroups" sub-module="create" role="button" class="btn btn-primary shadow" data-dismiss="modal">Create</a>
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#addInternalGroup").modal({show: true, backdrop: true});
	
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
				groupName: $("#groupName").val(),
				description: $("#description").val(),
				groupType: $("#groupType").val(),
				groupDn: $("#groupDn").val(),
				permission: $("#permission").val()
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
	
	$("#groupType").change(function(){
		event.preventDefault();
		
		if($("#groupType").val() == 1){
			$("#dnBlock").removeClass('d-none');
			$("#groupDn").addClass('form-validation');
		}else{
			$("#dnBlock").addClass('d-none');
			$("#groupDn").removeClass('form-validation');
			$("#groupDn").val('');
		}
	});
</script>
HTML;

print $htmlbody;
?>