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

	
		$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="addInternalUser" tabindex="-1" role="dialog" aria-labelledby="addInternalUser" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Reset User Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="groupName">Username:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation" id="userName" value="{$internalUser['userName']}" readonly>
			<div class="invalid-feedback">Please enter a valid Username</div>
		</div>
		<label class="fw-bold" for="password">Password:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="password" validation-state="required" class="form-control shadow form-validation my-password-field" id="password" value="">
			<div class="invalid-feedback">Please enter a password</div>
		</div>
		<label class="fw-bold" for="confirmpassword">Confirm Password:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="password" validation-state="required" class="form-control shadow form-validation" id="confirmpassword" value="">
			<div class="invalid-feedback">Please confirm your password</div>
			<div class="fw-bold small" id="passwordfeedback"></div>
		</div>
	  </div>
      <div class="modal-footer">
		<input type="hidden" id="id" value="{$internalUser['id']}">
		<a id="updatepass" href="#" module="internalusers" sub-module="updatepass" role="button" class="btn btn-primary shadow">Reset Password</a>
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#addInternalUser").modal('show');
	
	$("#updatepass").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('addInternalUser'));
			modal.hide();
		}
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
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
		}else{
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
	}
?>