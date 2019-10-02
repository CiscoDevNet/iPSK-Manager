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
			<div class="col text-center text-primary"><h5>Cisco ISE ERS Integration Settings</h5></div>
		</div>
		<label class="font-weight-bold" for="ersHost">Cisco ISE ERS Integration URL:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow iseers" id="ersHost" value="{$iseERSSettings['ersHost']}" placeholder="https://ise.demo.local:9060">
		</div>
		<label class="font-weight-bold" for="ersUsername">Cisco ISE ERS Integration Username:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow iseers" id="ersUsername" value="{$iseERSSettings['ersUsername']}">
		</div>
		<label class="font-weight-bold" for="ersPassword">Cisco ISE ERS Integration Pasword:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="password" class="form-control shadow" id="ersPassword" value="">
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update iseers" base-value="1" value="{$iseERSSettings['verify-ssl-peer']}" id="ersVerifySsl"{$iseERSSettings['verify-ssl-peer-check']}>
			<label class="custom-control-label" for="ersVerifySsl">Verify SSL Peer</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update iseers" base-value="1" value="{$iseERSSettings['enabled']}" id="ersEnabled"{$iseERSSettings['enabled-check']}>
			<label class="custom-control-label" for="ersEnabled">Cisco ISE ERS Integration Enabled</label>
		</div>
		<button id="updateersise" module="sysconfig" sub-module="update" module-action="ersupdate" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
		<button id="seterspass" module="sysconfig" sub-module="update" module-action="erspass" type="submit" class="btn btn-primary shadow" disabled>Set Password</button>
	</div>
	<div class="col m-3 shadow border border-secondary p-2">
		<div class="row">
			<div class="col text-center text-primary"><h5>Cisco ISE Monitoring Integration Settings</h5></div>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="mntHostPrimary">Cisco ISE MnT Integration URL:</label>
			<input type="text" class="form-control shadow isemnt" id="mntHostPrimary" value="{$iseMNTSettings['mntHost']}" placeholder="https://ise.demo.local">
		</div>
		<div class="form-group input-group-sm font-weight-bold d-none">
			<label class="font-weight-bold" for="mntHostSecondary">Cisco ISE MnT Integration Secondary Node Hostname:</label>
			<input type="text" class="form-control shadow isemnt" id="mntHostSecondary" value="">
		</div>
		<label class="font-weight-bold" for="mntUsername">Cisco ISE MnT Integration Username:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow isemnt" id="mntUsername" value="{$iseMNTSettings['mntUsername']}">
		</div>
		<label class="font-weight-bold" for="mntPassword">Cisco ISE MnT Integration Pasword:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="password" class="form-control shadow" id="mntPassword" value="">
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update isemnt" base-value="1" value="{$iseMNTSettings['verify-ssl-peer']}" id="mntVerifySsl"{$iseMNTSettings['verify-ssl-peer-check']}>
			<label class="custom-control-label" for="mntVerifySsl">Verify SSL Peer</label>
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update isemnt" base-value="1" value="{$iseMNTSettings['enabled']}" id="mntEnabled"{$iseMNTSettings['enabled-check']}>
			<label class="custom-control-label" for="mntEnabled">Cisco ISE MnT Integration Enabled</label>
		</div>
		<button id="updatemntise" module="sysconfig" sub-module="update" module-action="mntupdate" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
		<button id="setmntpass" module="sysconfig" sub-module="update" module-action="mntpass" type="submit" class="btn btn-primary shadow" disabled>Set Password</button>
	</div>
</div>
HTML;


?>