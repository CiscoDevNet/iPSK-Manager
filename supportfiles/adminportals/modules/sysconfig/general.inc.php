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
		<button id="updategeneral" module="sysconfig" sub-module="update" module-action="general" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
</div>
HTML;

?>
