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

		$ldapServer = $ipskISEDB->getLdapDirectoryById($id);
		
		if($ldapServer['adSecure'] == 1){
			$ldapServer['adSecureCheck'] = " checked";
		}else{
			$ldapServer['adSecureCheck'] = "";
		}

		if(isset($ldapServer['directoryType'])){
			if($ldapServer['directoryType'] == '0'){
				$ldapServer['directoryType-ad'] = " selected";
			}else{
				$ldapServer['directoryType-ad'] = "";
			}
			if($ldapServer['directoryType'] == '1'){
				$ldapServer['directoryType-openldap'] = " selected";
			}else{
				$ldapServer['directoryType-openldap'] = "";
			}
		}
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="editLdap" tabindex="-1" role="dialog" aria-labelledby="editLdapModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit LDAP Server</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="adConnectionName">Connection Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adConnectionName" value="{$ldapServer['adConnectionName']}">
			<div class="invalid-feedback">Please enter a Connection Name</div>
		</div>
		<label class="fw-bold" for="adDomain">Domain:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow " id="adDomain" value="{$ldapServer['adDomain']}">
		</div>
		<label class="fw-bold" for="adServer">Server:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adServer" value="{$ldapServer['adServer']}">
			<div class="invalid-feedback">Please enter a valid server</div>
		</div>
		<div class="row">
			<div class="col">
				<div class="form-check">
					<input type="checkbox" class="form-check-input checkbox-update" name="adSecure" base-value="1" value="{$ldapServer['adSecure']}" id="adSecure"{$ldapServer['adSecureCheck']}>
					<label class="form-check-label" for="adSecure">Secure LDAP</label>
					<small id="endpointGroupMembersBlock" class="form-text text-muted">Note: The server name used above must be in the certificate and the Root CA or Server Certificate must be Trusted by this Server for LDAPS unless SSL validation is disabled in platform configuration.</small>
				</div>
			</div>
		</div>
		<label class="fw-bold" for="adBaseDN">Search Base DN:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adBaseDN" value="{$ldapServer['adBaseDN']}">
			<div class="invalid-feedback">Please enter a valid search abse</div>
		</div>
		<label class="fw-bold" for="directoryType">Directory Type:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<select class="form-select form-select-sm shadow" id="directoryType">
				<option value="0"{$ldapServer['directoryType-ad']}>Active Directory</option>
				<option value="1"{$ldapServer['directoryType-openldap']}>OpenLDAP</option>
			</select>
		</div>	
		<label class="fw-bold" for="adUsername">Username:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" validation-state="required" class="form-control shadow form-validation my-password-field" id="adUsername" value="{$ldapServer['adUsername']}">
			<div class="invalid-feedback">Please enter a valid username, userPrincipalName is preffered.</div>
		</div>
		<label class="fw-bold" for="password">Password: <small class="form-text text-muted">(Note: Leave blank to keep current password)</small></label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="password" validation-state="" class="form-control shadow form-validation my-password-field" id="password" value="">
			<div class="invalid-feedback">Please enter a password</div>
		</div>
		<label class="fw-bold" for="confirmpassword">Confirm Password:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="password" validation-state="" class="form-control shadow form-validation" id="confirmpassword" value="" disabled>
			<div class="invalid-feedback">Please confirm your password</div>
			<div class="fw-bold small" id="passwordfeedback"></div>
		</div>
	  </div>
      <div class="modal-footer">
		<input type="hidden" id="id" value="{$ldapServer['id']}">
		<a id="update" href="#" module="ldap" sub-module="update" role="button" class="btn btn-primary shadow">Update</a>
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#editLdap").modal('show');
	
	$("#update").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('editLdap'));
			modal.hide();
		}
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
				adConnectionName: $("#adConnectionName").val(),
				adDomain: $("#adDomain").val(),
				adServer: $("#adServer").val(),
				adBaseDN: $("#adBaseDN").val(),
				adUsername: $("#adUsername").val(),
				adSecure: $("#adSecure").val(),
				password: $("#password").val(),
				confirmpassword: $("#confirmpassword").val(),
				directoryType: $("#directoryType").val()
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
		
		$("#password,#confirmpassword").attr('validation-state', 'required');
		
		if($("#confirmpassword").prop('disabled')){
			$("#confirmpassword").attr('disabled', false);
		}
		
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