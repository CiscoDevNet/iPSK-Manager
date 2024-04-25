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

		$internalUser = $ipskISEDB->getInternalUserById($id);
		
		if($internalUser['enabled'] == 1){
			$internalUser['enabled'] = " checked";
		}else{
			$internalUser['enabled'] = "";
		}
	
		$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="updateInternalUser" tabindex="-1" role="dialog" aria-labelledby="updateInternalUser" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Internal User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="userName">Username:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation" id="userName" value="{$internalUser['userName']}">
			<div class="invalid-feedback">Please enter a valid Username</div>
		</div>
		<label class="fw-bold" for="fullName">Full Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation" id="fullName" value="{$internalUser['fullName']}">
			<div class="invalid-feedback">Please enter a Name</div>
		</div>
		<label class="fw-bold" for="description">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="description" value="{$internalUser['description']}">
		</div>
		<label class="fw-bold" for="email">Email Address:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="email" value="{$internalUser['email']}">
		</div>
	  </div>
      <div class="modal-footer">
		<input type="hidden" id="id" value="{$internalUser['id']}">
		<a id="update" href="#" module="internalusers" sub-module="update" role="button" class="btn btn-primary shadow" data-bs-dismiss="modal">Update</a>
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#updateInternalUser").modal('show');
	
	$("#update").click(function(){
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
				id: $("#id").val(),
				userName: $("#userName").val(),
				fullName: $("#fullName").val(),
				description: $("#description").val(),
				email: $("#email").val()
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