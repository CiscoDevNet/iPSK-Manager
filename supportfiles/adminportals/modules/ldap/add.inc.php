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
<div class="modal fade" id="addLdap" tabindex="-1" role="dialog" aria-labelledby="addLdapModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add LDAP Server</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="adConnectionName">Connection Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adConnectionName" value="">
			<div class="invalid-feedback">Please enter a Connection Name</div>
		</div>
		<label class="font-weight-bold" for="adDomain">Domain:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow " id="adDomain" value="">
		</div>
		<label class="font-weight-bold" for="adServer">Server:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adServer" value="">
			<div class="invalid-feedback">Please enter a valid server</div>
		</div>
		<div class="form-row">
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" name="adSecure" base-value="1" value="0" id="adSecure">
					<label class="custom-control-label" for="adSecure">Secure LDAP</label>
				</div>
			</div>
		</div>
		<label class="font-weight-bold" for="adBaseDN">Search Base DN:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adBaseDN" value="">
			<div class="invalid-feedback">Please enter a valid search base</div>
		</div>
		<label class="font-weight-bold" for="adUsername">Username:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adUsername" value="">
			<div class="invalid-feedback">Please enter a valid username, userPrincipalName is preffered.</div>
		</div>
		<label class="font-weight-bold" for="password">Password:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="password" validation-state="required" class="form-control shadow form-validation my-password-field" id="password" value="">
			<div class="invalid-feedback">Please enter a password</div>
		</div>
		<label class="font-weight-bold" for="confirmpassword">Confirm Password:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="password" validation-state="required" class="form-control shadow form-validation" id="confirmpassword" value="">
			<div class="invalid-feedback">Please confirm your password</div>
			<div class="font-weight-bold small" id="passwordfeedback"></div>
		</div>
	  </div>
      <div class="modal-footer">
		<a id="create" href="#" module="ldap" sub-module="create" role="button" class="btn btn-primary shadow" data-dismiss="modal">Create</a>
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#addLdap").modal({show: true, backdrop: true});
	
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
				adConnectionName: $("#adConnectionName").val(),
				adDomain: $("#adDomain").val(),
				adServer: $("#adServer").val(),
				adBaseDN: $("#adBaseDN").val(),
				adUsername: $("#adUsername").val(),
				adSecure: $("#adSecure").val(),
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

	$(".checkbox-update").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));
		}else{
			$(this).attr('value', '0');
		}
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
?>