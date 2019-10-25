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
<div class="row">
	<div class="col m-3 shadow border border-secondary p-2">
		<div class="row">
			<div class="col text-center text-primary"><h5>Platform Logging Settings</h5></div>
		</div>
		<label class="font-weight-bold" for="adminPortalHostname">Logging Settings:</label>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update loggingtab" base-value="1" value="{$loggingSettings['sqlLogging']}" id="sqlLogging"{$loggingSettings['sqlLogging-check']}>
			<label class="custom-control-label text-danger" for="sqlLogging">Enable Logging to SQL</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update loggingtab" base-value="4" value="{$loggingSettings['payloadLogging']}" id="payloadLogging"{$loggingSettings['payloadLogging-check']}>
			<label class="custom-control-label text-danger" for="payloadLogging">Enable Logging of Debug Payload to SQL</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update loggingtab" base-value="16" value="{$loggingSettings['debugLogging']}" id="debugLogging"{$loggingSettings['debugLogging-check']}>
			<label class="custom-control-label text-danger" for="debugLogging">Enable Logging of Debug Variables to SQL</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update loggingtab" base-value="32" value="{$loggingSettings['getLogging']}" id="getLogging"{$loggingSettings['getLogging-check']}>
			<label class="custom-control-label text-danger" for="getLogging">Log PHP '_GET' Variable to Debug Payload</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update loggingtab" base-value="64" value="{$loggingSettings['postLogging']}" id="postLogging"{$loggingSettings['postLogging-check']}>
			<label class="custom-control-label text-danger" for="postLogging">Log PHP '_POST' Variable to Debug Payload</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update loggingtab" base-value="128" value="{$loggingSettings['sessionLogging']}" id="sessionLogging"{$loggingSettings['sessionLogging-check']}>
			<label class="custom-control-label text-danger" for="sessionLogging">Log PHP '_SESSION' Variable to Debug Payload</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update loggingtab" base-value="256" value="{$loggingSettings['serverLogging']}" id="serverLogging"{$loggingSettings['serverLogging-check']}>
			<label class="custom-control-label text-danger" for="serverLogging">Log PHP '_SERVER' Variable to Debug Payload</label>
		</div>
		<button id="updatelogging" module="sysconfig" sub-module="update" module-action="loggingupdate" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
	</div>
</div>
HTML;

?>
