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




	$advancedSettings = $ipskISEDB->getGlobalSetting("platform-config","logging-level");
	
	if($advancedSettings & 1){
		$loggingSettings['sqlLogging'] = '1';
		$loggingSettings['sqlLogging-check'] = ' checked';
	}else{
		$loggingSettings['sqlLogging'] = '';
		$loggingSettings['sqlLogging-check'] = '';
	}
	
	if($advancedSettings & 4){
		$loggingSettings['payloadLogging'] = '4';
		$loggingSettings['payloadLogging-check'] = ' checked';
	}else{
		$loggingSettings['payloadLogging'] = '';
		$loggingSettings['payloadLogging-check'] = '';
	}
	
	if($advancedSettings & 16){
		$loggingSettings['debugLogging'] = '16';
		$loggingSettings['debugLogging-check'] = ' checked';
	}else{
		$loggingSettings['debugLogging'] = '';
		$loggingSettings['debugLogging-check'] = '';
	}
	
	if($advancedSettings & 32){
		$loggingSettings['getLogging'] = '32';
		$loggingSettings['getLogging-check'] = ' checked';
	}else{
		$loggingSettings['getLogging'] = '';
		$loggingSettings['getLogging-check'] = '';
	}
	
	if($advancedSettings & 64){
		$loggingSettings['postLogging'] = '64';
		$loggingSettings['postLogging-check'] = ' checked';
	}else{
		$loggingSettings['postLogging'] = '';
		$loggingSettings['postLogging-check'] = '';
	}
	
	if($advancedSettings & 128){
		$loggingSettings['sessionLogging'] = '128';
		$loggingSettings['sessionLogging-check'] = ' checked';
	}else{
		$loggingSettings['sessionLogging'] = '';
		$loggingSettings['sessionLogging-check'] = '';
	}
	
	if($advancedSettings & 256){
		$loggingSettings['serverLogging'] = '256';
		$loggingSettings['serverLogging-check'] = ' checked';
	}else{
		$loggingSettings['serverLogging'] = '';
		$loggingSettings['serverLogging-check'] = '';
	}
	
	print <<< HTML
<div class="container-fluid">
	<div class="row row-cols-1 row-cols-md-2 g-4">
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">Platform Logging Settings</div>
          		<div class="card-body">
				  <p class="card-text fst-italic text-danger">Note: Change logging settings with caution. Most options are for debugging issues.</p>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update loggingtab" base-value="1" value="{$loggingSettings['sqlLogging']}" id="sqlLogging"{$loggingSettings['sqlLogging-check']}>
						<label class="form-check-label" for="sqlLogging">Enable Logging to SQL</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update loggingtab" base-value="4" value="{$loggingSettings['payloadLogging']}" id="payloadLogging"{$loggingSettings['payloadLogging-check']}>
						<label class="form-check-label" for="payloadLogging">Enable Logging of Debug Payload to SQL</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update loggingtab" base-value="16" value="{$loggingSettings['debugLogging']}" id="debugLogging"{$loggingSettings['debugLogging-check']}>
						<label class="form-check-label" for="debugLogging">Enable Logging of Debug Variables to SQL</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update loggingtab" base-value="32" value="{$loggingSettings['getLogging']}" id="getLogging"{$loggingSettings['getLogging-check']}>
						<label class="form-check-label" for="getLogging">Log PHP '_GET' Variable to Debug Payload</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update loggingtab" base-value="64" value="{$loggingSettings['postLogging']}" id="postLogging"{$loggingSettings['postLogging-check']}>
						<label class="form-check-label" for="postLogging">Log PHP '_POST' Variable to Debug Payload</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update loggingtab" base-value="128" value="{$loggingSettings['sessionLogging']}" id="sessionLogging"{$loggingSettings['sessionLogging-check']}>
						<label class="form-check-label" for="sessionLogging">Log PHP '_SESSION' Variable to Debug Payload</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update loggingtab" base-value="256" value="{$loggingSettings['serverLogging']}" id="serverLogging"{$loggingSettings['serverLogging-check']}>
						<label class="form-check-label" for="serverLogging">Log PHP '_SERVER' Variable to Debug Payload</label>
					</div>	
				</div>
				<div class="card-footer">
					<button id="updatelogging" module="sysconfig" sub-module="update" module-action="loggingupdate" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
?>
