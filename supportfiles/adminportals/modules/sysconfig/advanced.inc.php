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
<div class="container-fluid">
	<div class="row row-cols-1 row-cols-md-2 g-4">
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">Advanced Platform Settings</div>
          		<div class="card-body">
					<div class="form-label">
						<input type="checkbox" class="form-check-input checkbox-update advancedtab" base-value="1" value="{$advancedSettings['enable-portal-psk-edit-value']}" id="portalPskEditEnabled"{$advancedSettings['enable-portal-psk-edit']}>
						<label class="form-check-label" for="portalPskEditEnabled">Enable the "Manual PSK Editing" Portal Group Permission</label>
					</div>
					<div class="form-label">
						<input type="checkbox" class="form-check-input checkbox-update advancedtab" base-value="1" value="{$advancedSettings['enable-advanced-logging-value']}" id="advancedLoggingSettings"{$advancedSettings['enable-advanced-logging']}>
						<label class="form-check-label" for="advancedLoggingSettings">Enable Platform Logging Settings <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Use with caution. Changing logging settings should be used for debugging purposes only." data-bs-placement="right"><i data-feather="alert-triangle"></i></a></label>
					</div>			
				</div>
				<div class="card-footer">
					<button id="updateadvanced" module="sysconfig" sub-module="update" module-action="advancedupdate" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
?>
