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
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewldapserver" tabindex="-1" role="dialog" aria-labelledby="viewldapserverModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">View LDAP Server</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="adConnectionName">Connection Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="adConnectionName" value="{$ldapServer['adConnectionName']}" readonly>
		</div>
		<label class="font-weight-bold" for="adDomain">Domain:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="adDomain" value="{$ldapServer['adDomain']}" readonly>
		</div>
		<label class="font-weight-bold" for="adServer">Server:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="adServer" value="{$ldapServer['adServer']}" readonly>
		</div>
		<div class="form-row">
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" name="permission" base-value="1" value="{$ldapServer['adSecure']}" id="adSecure"{$ldapServer['adSecureCheck']}>
					<label class="custom-control-label" for="adSecure">Secure LDAP</label>
				</div>
			</div>
		</div>
		<label class="font-weight-bold" for="adBaseDN">Search Base DN:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="adBaseDN" value="{$ldapServer['adBaseDN']}" readonly>
		</div>
		<label class="font-weight-bold" for="adUsername">Username:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="adUsername" value="{$ldapServer['adUsername']}" readonly>
		</div>
		<label class="font-weight-bold" for="createdBy">Date Created:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$ldapServer['createdDate']}" readonly>
		</div>
		<label class="font-weight-bold" for="createdBy">Created By:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="createdBy" value="{$ldapServer['createdBy']}" readonly>
		</div>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#viewldapserver").modal();

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