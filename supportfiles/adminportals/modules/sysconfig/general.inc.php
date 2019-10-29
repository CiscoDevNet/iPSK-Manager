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

$alphabetArray = Array(1=>"abcdefghijkmnopqrstuvwxyz", 2=>"ABCDEFGHJKLMNPQRSTUVWXYZ", 4=>"123456789", 8=>'!?#$%@*()',16=>'lIO0');
	print <<< HTML
<div class="row">
	<div class="col m-3 shadow border border-secondary p-2">
		<div class="row">
			<div class="col text-center text-primary"><h5>General Platform Settings</h5></div>
		</div>
		<label class="font-weight-bold" for="adminPortalHostname">Administration Portal Hostname:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow generaltab" id="adminPortalHostname" value="{$adminPortalSettings['admin-portal-hostname']}">
		</div>
		<div class="form-group input-group-sm font-weight-bold d-none">
			<label class="font-weight-bold" for="loggingLevel">Logging:</label>
			<select id="loggingLevel" class="form-control mt-2 mb-3 shadow generaltab">
				<option value="0">No Logging</option>
				<option value="1">1 Day</option>
				
			</select>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update generaltab" base-value="1" value="{$adminPortalSettings['admin-portal-strict-hostname-value']}" id="strictHostname"{$adminPortalSettings['admin-portal-strict-hostname']}>
			<label class="custom-control-label" for="strictHostname">Enable Administration Portal Strict Hostname Matching</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update generaltab" base-value="1" value="{$adminPortalSettings['redirect-on-hostname-match-value']}" id="redirectOnHostname"{$adminPortalSettings['redirect-on-hostname-match']}>
			<label class="custom-control-label" for="redirectOnHostname">Redirect to Portal on Hostname Match</label>
		</div>
		<button id="updateGeneral" module="sysconfig" sub-module="update" module-action="general" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
	<div class="col m-3 shadow border border-secondary p-2">
		<div class="row">
			<div class="col text-center text-primary"><h5>Global Password/Pre-Shared Key Complexity Settings</h5><h6 class="text-danger">Note: Having no options selected will default to the first four options.</div>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update complexitytab" base-value="1" value="{$adminPortalSettings['complexity-lower-value']}" id="complexLowercase"{$adminPortalSettings['complexity-lower']}>
			<label class="custom-control-label" for="complexLowercase">Enable Lower Case [ <span class="text-danger font-weight-bold">abcdefghijkmnopqrstuvwxyz</span> ]</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update complexitytab" base-value="2" value="{$adminPortalSettings['complexity-upper-value']}" id="complexUppercase"{$adminPortalSettings['complexity-upper']}>
			<label class="custom-control-label" for="complexUppercase">Enable Upper Case [ <span class="text-danger font-weight-bold">ABCDEFGHJKLMNPQRSTUVWXYZ</span> ]</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update complexitytab" base-value="4" value="{$adminPortalSettings['complexity-number-value']}" id="complexNumbers"{$adminPortalSettings['complexity-number']}>
			<label class="custom-control-label" for="complexNumbers">Enable Numbers [ <span class="text-danger font-weight-bold">123456789</span> ]</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update complexitytab" base-value="8" value="{$adminPortalSettings['complexity-special-value']}" id="complexSpecial"{$adminPortalSettings['complexity-special']}>
			<label class="custom-control-label" for="complexSpecial">Enable Special Characters [ <span class="text-danger font-weight-bold">!?#$%@*()</span> ]</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update complexitytab" base-value="16" value="{$adminPortalSettings['complexity-similar-value']}}" id="complexSimilar"{$adminPortalSettings['complexity-similar']}>
			<label class="custom-control-label" for="complexSimilar">Enable Similar Characters [ <span class="text-danger font-weight-bold">lIO0</span> ]</label>
		</div>
		<button id="updateComplexity" module="sysconfig" sub-module="update" module-action="complexity" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
</div>
HTML;

?>
