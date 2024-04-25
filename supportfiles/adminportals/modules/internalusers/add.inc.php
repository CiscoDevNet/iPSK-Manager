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
	

	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="addInternalUser" tabindex="-1" role="dialog" aria-labelledby="addInternalUserModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Internal User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="groupName">Username:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation" id="userName" value="">
			<div class="invalid-feedback">Please enter a valid Username</div>
		</div>
		<label class="fw-bold" for="fullName">Full Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation" id="fullName" value="">
			<div class="invalid-feedback">Please enter a Name</div>
		</div>
		<label class="fw-bold" for="description">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="description" value="">
		</div>
		<label class="fw-bold" for="email">Email Address:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="email" value="">
		</div>
		<label class="fw-bold" for="password">Password:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="password" validation-state="required" validation-minimum-length="6" class="form-control shadow form-validation my-password-field" id="password" value="">
			<div class="invalid-feedback">Please enter a password</div>
		</div>
		<label class="fw-bold" for="confirmpassword">Confirm Password:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="password" validation-state="required" validation-minimum-length="6" class="form-control shadow form-validation" id="confirmpassword" value="">
			<div class="invalid-feedback">Please confirm your password</div>
			<div class="fw-bold small" id="passwordfeedback"></div>
		</div>
	  </div>
      <div class="modal-footer">
		<a id="create" href="#" module="internalusers" sub-module="create" role="button" class="btn btn-primary shadow" data-bs-dismiss="modal">Create</a>
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#addInternalUser").modal('show');
	
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
				userName: $("#userName").val(),
				fullName: $("#fullName").val(),
				description: $("#description").val(),
				email: $("#email").val(),
				password: $("#password").val(),
				confirmpassword: $("#confirmpassword").val()
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
	
	$("#password,#confirmpassword").keyup(function(event){
		var pass = $("#password").val();
		var confirmpass = $("#confirmpassword").val();

		if(pass != confirmpass){
			$("#passwordfeedback").removeClass('text-success');
			$("#passwordfeedback").addClass('text-danger');
			$("#passwordfeedback").html('Passwords must match and be at least 6 characters long!');	
		}
		else if(pass.length < 6) {
			$("#passwordfeedback").removeClass('text-success');
			$("#passwordfeedback").addClass('text-danger');
			$("#passwordfeedback").html('Passwords must be at least 6 characters long!');
		}
		else{
			$("#passwordfeedback").addClass('text-success');
			$("#passwordfeedback").removeClass('text-danger');
			$("#passwordfeedback").html('Passwords Match!');
		}
		pass = "";
		confirmpass = "";
		
	});
	

</script>
HTML;

print $htmlbody;
?>