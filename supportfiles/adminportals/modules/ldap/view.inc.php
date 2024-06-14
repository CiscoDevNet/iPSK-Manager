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
		
		$ldapServer['createdBy'] = $ipskISEDB->getUserPrincipalNameFromCache($ldapServer['createdBy']);

		$ldapServer['createdDate'] = date($globalDateOutputFormat, strtotime($ldapServer['createdDate']));

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
<div class="modal fade" id="viewldapserver" tabindex="-1" role="dialog" aria-labelledby="viewldapserverModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View LDAP Server</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="adConnectionName">Connection Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="adConnectionName" value="{$ldapServer['adConnectionName']}" readonly>
		</div>
		<label class="fw-bold" for="adDomain">Domain:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="adDomain" value="{$ldapServer['adDomain']}" readonly>
		</div>
		<label class="fw-bold" for="adServer">Server:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="adServer" value="{$ldapServer['adServer']}" readonly>
		</div>
		<div class="row">
			<div class="col">
				<div class="form-check">
					<input type="checkbox" class="form-check-input checkbox-update" name="permission" base-value="1" value="{$ldapServer['adSecure']}" id="adSecure"{$ldapServer['adSecureCheck']}>
					<label class="form-check-label" for="adSecure">Secure LDAP</label>
				</div>
			</div>
		</div>
		<label class="fw-bold" for="adBaseDN">Search Base DN:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="adBaseDN" value="{$ldapServer['adBaseDN']}" readonly>
		</div>
		<label class="fw-bold" for="directoryType">Directory Type:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<select class="form-select form-select-sm shadow" id="directoryType" disabled>
				<option value="0"{$ldapServer['directoryType-ad']}>Active Directory</option>
				<option value="1"{$ldapServer['directoryType-openldap']}>OpenLDAP</option>
			</select>
		</div>		
		<label class="fw-bold" for="adUsername">Username:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="adUsername" value="{$ldapServer['adUsername']}" readonly>
		</div>
		<label class="fw-bold" for="createdBy">Date Created:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$ldapServer['createdDate']}" readonly>
		</div>
		<label class="fw-bold" for="createdBy">Created By:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$ldapServer['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewldapserver").modal('show');
	
	$(function() {	
		feather.replace()
	});
	$("#showpassword").on('click', function(event) {
		event.preventDefault();
		if($("#presharedKey").attr('type') == "text"){
			$("#presharedKey").attr('type', 'password');
			$("#passwordfeather").attr('data-feather','eye');
			feather.replace();
		}else if($("#presharedKey").attr('type') == "password"){
			$("#presharedKey").attr('type', 'text');
			$("#passwordfeather").attr('data-feather','eye-off');
			feather.replace();
		}
	});
</script>
HTML;

		print $htmlbody;
	}
?>