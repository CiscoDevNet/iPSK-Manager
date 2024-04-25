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


	print <<< HTML
<div class="row">
	<div class="col m-3 shadow border border-secondary p-2">
		<div class="row">
			<div class="col text-center text-primary"><h5>Advanced Platform Settings</h5></div>
		</div>
		<label class="fw-bold" for="adminPortalHostname">Advanced Settings:</label>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update advancedtab" base-value="1" value="{$advancedSettings['enable-portal-psk-edit-value']}" id="portalPskEditEnabled"{$advancedSettings['enable-portal-psk-edit']}>
			<label class="form-check-label text-danger" for="portalPskEditEnabled"><strong>Enable the "Manual PSK Editing" Portal Group Permission</strong></label>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update advancedtab" base-value="1" value="{$advancedSettings['enable-advanced-logging-value']}" id="advancedLoggingSettings"{$advancedSettings['enable-advanced-logging']}>
			<label class="form-check-label text-danger" for="advancedLoggingSettings"><strong>Enable Platform Logging Settings</strong></label>
		</div>
		<button id="updateadvanced" module="sysconfig" sub-module="update" module-action="advancedupdate" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
</div>
HTML;

?>
