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
			<div class="col text-center text-primary"><h5>SMTP Settings</h5></div>
		</div>
		<label class="fw-bold" for="smtpHost">SMTP Hostname/IP:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpHost" value="{$smtpSettings['smtp-hostname']}" placeholder="smtp.demo.local">
		</div>
		<label class="fw-bold" for="smtpPort">SMTP Port:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpPort" value="{$smtpSettings['smtp-port']}" placeholder="e.g. 25">
		</div>
		<label class="fw-bold" for="smtpUsername">SMTP Username:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpUsername" value="{$smtpSettings['smtp-username']}">
		</div>
		<label class="fw-bold" for="smtpFromAddress">From Address:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow smtpupdate" id="smtpFromAddress" value="{$smtpSettings['smtp-fromaddress']}">
		</div>
		<label class="fw-bold" for="smtpPassword">SMTP Pasword:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="password" class="form-control shadow" id="smtpPassword">
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input checkbox-update smtpupdate" base-value="1" value="{$smtpSettings['enabled']}" id="smtpEnabled"{$smtpSettings['enabled-check']}>
			<label class="form-check-label" for="smtpEnabled">SMTP Email Enabled</label>
		</div>
		<button id="updatesmtp" module="sysconfig" sub-module="update" module-action="smtpupdate" type="submit" class="btn btn-primary shadow" disabled>Update Settings</button>
		<button id="setsmtppass" module="sysconfig" sub-module="update" module-action="smtppass" type="submit" class="btn btn-primary shadow" disabled>Set Password</button>
	</div>
</div>
HTML;


?>