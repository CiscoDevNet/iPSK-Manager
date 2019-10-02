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
			<div class="col text-center text-primary"><h5>SMTP Settings</h5></div>
		</div>
		<label class="font-weight-bold" for="smtpHost">SMTP Hostname/IP:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpHost" value="{$smtpSettings['smtp-hostname']}" placeholder="smtp.demo.local">
		</div>
		<label class="font-weight-bold" for="smtpPort">SMTP Port:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpPort" value="{$smtpSettings['smtp-port']}" placeholder="e.g. 25">
		</div>
		<label class="font-weight-bold" for="smtpUsername">SMTP Username:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpUsername" value="{$smtpSettings['smtp-username']}">
		</div>
		<label class="font-weight-bold" for="smtpFromAddress">From Address:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpFromAddress" value="{$smtpSettings['smtp-fromaddress']}">
		</div>
		<label class="font-weight-bold" for="smtpPassword">SMTP Pasword:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="password" class="form-control shadow" id="smtpPassword">
		</div>
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input checkbox-update smtpupdate" base-value="1" value="{$smtpSettings['enabled']}" id="smtpEnabled"{$smtpSettings['enabled-check']}>
			<label class="custom-control-label" for="smtpEnabled">SMTP Email Enabled</label>
		</div>
		<button id="updatesmtp" module="sysconfig" sub-module="update" module-action="smtpupdate" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
		<button id="setsmtppass" module="sysconfig" sub-module="update" module-action="smtppass" type="submit" class="btn btn-primary shadow" disabled>Set Password</button>
	</div>
</div>
HTML;


?>