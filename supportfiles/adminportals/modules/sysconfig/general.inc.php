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
<div class="container-fluid">
	<div class="row row-cols-1 row-cols-md-2 g-4">
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">General Platform Settings</div>
          		<div class="card-body">
		  			<label class="form-label" for="adminPortalHostname">Administration Portal Hostname:</label>
					<div class="mb-3 input-group-sm fw-bold w-75">
						<input type="text" class="form-control shadow generaltab" id="adminPortalHostname" value="{$adminPortalSettings['admin-portal-hostname']}">
					</div>
					<div class="mb-3 input-group-sm fw-bold d-none">
						<label class="fw-bold" for="loggingLevel">Logging:</label>
						<select id="loggingLevel" class="form-select mt-2 mb-3 shadow generaltab">
							<option value="0">No Logging</option>
							<option value="1">1 Day</option>
						</select>
					</div>
					<label class="form-label" for="logPurgeInterval">Log Purge Interval in Days: <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="When logging into the admin portal logs older then number of days set will be purged.  If no value is defined logs are not purged." data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					<div class="mb-3 input-group-sm fw-bold w-75">
						<input type="text" class="form-control shadow generaltab" id="logPurgeInterval" value="{$adminPortalSettings['log-purge-interval']}">
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
						<input type="checkbox" class="form-check-input checkbox-update generaltab" base-value="1" value="{$adminPortalSettings['use-portal-description-value']}" id="usePortalDescription"{$adminPortalSettings['use-portal-description']}>
						<label class="form-check-label" for="usePortalDescription">Use Portal Description on Sponsor Portal Pages</label>
					</div>
				</div>
				<div class="card-footer">
					<button id="updateGeneral" module="sysconfig" sub-module="update" module-action="general" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">LDAP Settings</div>
          		<div class="card-body">
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update ldaptab" base-value="1" value="{$ldapSettings['ldap-ssl-check-value']}" id="ldapSSLCheck"{$ldapSettings['ldap-ssl-check']}>
						<label class="form-check-label" for="ldapSSLCheck">Disable LDAP SSL Validation <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="This option will disable all SSL certificate validation for all LDAP servers. Certificates will no longer have to be trusted." data-bs-placement="right"><i data-feather="alert-triangle"></i></a></label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update ldaptab" base-value="1" value="{$ldapSettings['nested-groups-value']}" id="nestedGroups"{$ldapSettings['nested-groups']}>
						<label class="form-check-label" for="nestedGroups">Enable AD LDAP Nested Group Support <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Nested group support requires multiple queries to LDAP directory on every login. Users with many standard or nested groups may notice a delay in logging in. See README.md for more information." data-bs-placement="right"><i data-feather="alert-triangle"></i></a></label>
					</div>
				</div>
				<div class="card-footer">
					<button id="updateLdap" module="sysconfig" sub-module="update" module-action="ldap" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">SAML Settings</div>
          		<div class="card-body">
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update samltab" base-value="1" value="{$samlSettings['enabled-value']}" id="samlEnabled"{$samlSettings['enabled']}>
						<label class="form-check-label" for="samlEnabled">Enable SAML Authentication <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Enabling SAML requires an external SP to function. See README.md for more information on SAML before enabling." data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					</div>	
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update samltab" base-value="1" value="{$samlSettings['headers-value']}" id="samlHeaders"{$samlSettings['headers']}>
						<label class="form-check-label" for="samlHeaders">Use Headers Not Enviroment Variables For SAML <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Use with caution. Users can inject headers into requests.  Be sure to strip incoming header if sent in client request." data-bs-placement="right"><i data-feather="alert-triangle"></i></a></label>
					</div>
HTML;	
				if($ipskISEDB->getLdapDirectoryCount() > 0){
					$ldapListing = $ipskISEDB->getLdapDirectoryListing();
					print <<< HTML
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update samltab" base-value="1" value="{$samlSettings['ldap-source-value']}" id="samlLdapSource"{$samlSettings['ldap-source']}>
						<label class="form-check-label" for="samlLdapSource">Use LDAP for Admin Portal SAML Authorization <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="SAML authorization requires mapping the authenticated username to a group. By default the Admin Portal will use the internal user/group database. To use LDAP check box and select LDAP directory below. Other portals use the portal group authentication setting for authorization source." data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					</div>
					<div class="mb-3" id="samlDirectory">
						<label class="form-label" for="samlLdapSourceDirectory">Select LDAP Server For SAML Authorization:</label>		
						<select class="form-select form-select-sm shadow samltab w-50" id="samlLdapSourceDirectory">
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
					
					<label class="form-label" for="samlUsernameVariable">Override SAML Username Attribute Name: <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Default attribute is REMOTE_USER to change enter new value" data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					<div class="mb-2 input-group-sm fw-bold w-50">
						<input type="text" class="form-control shadow samltab" id="samlUsernameVariable" value="{$samlSettings['usernamefield']}">
					</div>
				</div>
				<div class="card-footer">
					<button id="updateSaml" module="sysconfig" sub-module="update" module-action="saml" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
				</div>
        	</div>
        </div>
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">Global Password/Pre-Shared Key Complexity Settings</div>
          		<div class="card-body">
		  			<p class="card-text fst-italic">Note: Having no options selected will default to the first four options.</p>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="1" value="{$adminPortalSettings['complexity-lower-value']}" id="complexLowercase"{$adminPortalSettings['complexity-lower']}>
						<label class="form-check-label" for="complexLowercase">Enable Lower Case [ <span class="fw-bold">abcdefghijkmnopqrstuvwxyz</span> ]</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="2" value="{$adminPortalSettings['complexity-upper-value']}" id="complexUppercase"{$adminPortalSettings['complexity-upper']}>
						<label class="form-check-label" for="complexUppercase">Enable Upper Case [ <span class="fw-bold">ABCDEFGHJKLMNPQRSTUVWXYZ</span> ]</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="4" value="{$adminPortalSettings['complexity-number-value']}" id="complexNumbers"{$adminPortalSettings['complexity-number']}>
						<label class="form-check-label" for="complexNumbers">Enable Numbers [ <span class="fw-bold">123456789</span> ]</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="8" value="{$adminPortalSettings['complexity-special-value']}" id="complexSpecial"{$adminPortalSettings['complexity-special']}>
						<label class="form-check-label" for="complexSpecial">Enable Special Characters [ <span class="fw-bold">!?#$%@*()</span> ]</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update complexitytab" base-value="16" value="{$adminPortalSettings['complexity-similar-value']}}" id="complexSimilar"{$adminPortalSettings['complexity-similar']}>
						<label class="form-check-label" for="complexSimilar">Enable Similar Characters [ <span class="fw-bold">lIO0</span> ]</label>
					</div>
				</div>
				<div class="card-footer">
					<button id="updateComplexity" module="sysconfig" sub-module="update" module-action="complexity" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
?>
