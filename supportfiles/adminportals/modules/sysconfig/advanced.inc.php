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
			<div class="col text-center text-primary"><h5>Advanced Platform Settings</h5></div>
		</div>
		<label class="font-weight-bold" for="adminPortalHostname">Advanced Settings:</label>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update advancedtab" base-value="1" value="{$advancedSettings['enable-portal-psk-edit-value']}" id="portalPskEditEnabled"{$advancedSettings['enable-portal-psk-edit']}>
			<label class="custom-control-label text-danger" for="portalPskEditEnabled"><strong>Enable per portal proup PSK editing functionality</strong></label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update advancedtab" base-value="1" value="{$advancedSettings['enable-advanced-logging-value']}" id="advancedLoggingSettings"{$advancedSettings['enable-advanced-logging']}>
			<label class="custom-control-label text-danger" for="advancedLoggingSettings"><strong>Enable Platform Logging Settings</strong></label>
		</div>
		<button id="updateadvanced" module="sysconfig" sub-module="update" module-action="advancedupdate" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
</div>
HTML;

?>
