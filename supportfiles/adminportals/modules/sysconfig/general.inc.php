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

$alphabetArray = Array(1=>"abcdefghijkmnopqrstuvwxyz", 2=>"ABCDEFGHJKLMNPQRSTUVWXYZ", 4=>"123456789", 8=>'!?#$%@*()',16=>'lIO0');
	print <<< HTML
<div class="row">
	<div class="col m-3 shadow border border-secondary p-2">
		<div class="row">
			<div class="col text-center text-primary"><h5>General Platform Settings</h5></div>
		</div>
		<label class="fw-bold" for="adminPortalHostname">Administration Portal Hostname:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow generaltab" id="adminPortalHostname" value="{$adminPortalSettings['admin-portal-hostname']}">
		</div>
		<div class="mb-3 input-group-sm fw-bold d-none">
			<label class="fw-bold" for="loggingLevel">Logging:</label>
			<select id="loggingLevel" class="form-select mt-2 mb-3 shadow generaltab">
				<option value="0">No Logging</option>
				<option value="1">1 Day</option>
				
			</select>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update generaltab" base-value="1" value="{$adminPortalSettings['admin-portal-strict-hostname-value']}" id="strictHostname"{$adminPortalSettings['admin-portal-strict-hostname']}>
			<label class="form-check-label" for="strictHostname">Enable Administration Portal Strict Hostname Matching</label>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update generaltab" base-value="1" value="{$adminPortalSettings['redirect-on-hostname-match-value']}" id="redirectOnHostname"{$adminPortalSettings['redirect-on-hostname-match']}>
			<label class="form-check-label" for="redirectOnHostname">Redirect to Portal on Hostname Match</label>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update generaltab" base-value="1" value="{$ldapSettings['ldap-ssl-check-value']}" id="ldapSSLCheck"{$ldapSettings['ldap-ssl-check']}>
			<label class="form-check-label" for="ldapSSLCheck">Disable LDAP SSL Validation</label>
		</div>
		<div class="row">
			<div class="col text-center text-primary"><br /></div>
		</div>
		<div class="row">
			<div class="col text-center text-primary"><h5>SAML Settings</h5></div>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update generaltab" base-value="1" value="{$samlSettings['enabled-value']}" id="samlEnabled"{$samlSettings['enabled']}>
			<label class="form-check-label" for="samlEnabled">Enable SAML Authentication</label>
		</div>
		
HTML;
				
				if($ipskISEDB->getLdapDirectoryCount() > 0){
					$ldapListing = $ipskISEDB->getLdapDirectoryListing();
					print <<< HTML
					<div class="form-check">
					<input type="checkbox" class="form-check-input checkbox-update generaltab" base-value="1" value="{$samlSettings['ldap-source-value']}" id="samlLdapSource"{$samlSettings['ldap-source']}>
					<label class="form-check-label" for="samlLdapSource">Use LDAP as SAML User Source For Admin Portal (Other Portals Use Portal Settings)</label>
					</div>
					<br />
					<div class="mb-3 fw-bold">
					<label class="fw-bold" for="samlLdapSourceDirectory">Select LDAP Server For SAML (LDAP as SAML User Source Must Be Checked):</label>		
					<select class="form-select shadow generaltab" id="samlLdapSourceDirectory">
					HTML;
					while($row = $ldapListing->fetch_assoc()){
						if($row['id'] == $samlSettings['ldap-source-directory']){
							print "<option value=\"".$row['id']."\" selected>".$row['adConnectionName']."</option>";
						}else{
							print "<option value=\"".$row['id']."\">".$row['adConnectionName']."</option>";
						}
					}
					print <<< HTML

					</select>
					</div>
					HTML;	
				}
						
	print <<< HTML
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update generaltab" base-value="1" value="{$samlSettings['headers-value']}" id="samlHeaders"{$samlSettings['headers']}>
			<label class="form-check-label" for="samlHeaders">Use Headers Not Enviroment Variables For SAML</label>
		</div>
		<div class="row">
			<div class="col text-center text-primary"><br /></div>
		</div>
		<label class="fw-bold" for="samlUsernameVariable">Override SAML Username Attribute Name (Defaults to REMOTE_USER):</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow generaltab" id="samlUsernameVariable" value="{$samlSettings['usernamefield']}">
		</div>
		<button id="updateGeneral" module="sysconfig" sub-module="update" module-action="general" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
	<div class="col m-3 shadow border border-secondary p-2">
		<div class="row">
			<div class="col text-center text-primary"><h5>Global Password/Pre-Shared Key Complexity Settings</h5><h6 class="text-danger">Note: Having no options selected will default to the first four options.</div>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="1" value="{$adminPortalSettings['complexity-lower-value']}" id="complexLowercase"{$adminPortalSettings['complexity-lower']}>
			<label class="form-check-label" for="complexLowercase">Enable Lower Case [ <span class="text-danger fw-bold">abcdefghijkmnopqrstuvwxyz</span> ]</label>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="2" value="{$adminPortalSettings['complexity-upper-value']}" id="complexUppercase"{$adminPortalSettings['complexity-upper']}>
			<label class="form-check-label" for="complexUppercase">Enable Upper Case [ <span class="text-danger fw-bold">ABCDEFGHJKLMNPQRSTUVWXYZ</span> ]</label>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="4" value="{$adminPortalSettings['complexity-number-value']}" id="complexNumbers"{$adminPortalSettings['complexity-number']}>
			<label class="form-check-label" for="complexNumbers">Enable Numbers [ <span class="text-danger fw-bold">123456789</span> ]</label>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="8" value="{$adminPortalSettings['complexity-special-value']}" id="complexSpecial"{$adminPortalSettings['complexity-special']}>
			<label class="form-check-label" for="complexSpecial">Enable Special Characters [ <span class="text-danger fw-bold">!?#$%@*()</span> ]</label>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="16" value="{$adminPortalSettings['complexity-similar-value']}}" id="complexSimilar"{$adminPortalSettings['complexity-similar']}>
			<label class="form-check-label" for="complexSimilar">Enable Similar Characters [ <span class="text-danger fw-bold">lIO0</span> ]</label>
		</div>
		<button id="updateComplexity" module="sysconfig" sub-module="update" module-action="complexity" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
</div>
HTML;

?>
