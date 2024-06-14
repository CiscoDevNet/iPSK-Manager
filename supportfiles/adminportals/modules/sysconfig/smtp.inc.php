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
          		<div class="card-header bg-primary text-white">Email Configuration Settings</div>
          		<div class="card-body">
				  	<label class="form-label" for="smtpFromAddress">From Address: <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Set the from address when iPSK Manager sends emails.  Setting is used when using underlying system Mail Transfer Agent (MTA) or SMTP to send emails." data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					<div class="mb-3 input-group-sm w-75">
						<input type="text" class="form-control shadow smtpupdate" id="smtpFromAddress" value="{$smtpSettings['smtp-fromaddress']}">
					</div>
				  	<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update smtpupdate" base-value="1" value="{$smtpSettings['enabled']}" id="smtpEnabled"{$smtpSettings['enabled-check']}>
						<label class="form-check-label" for="smtpEnabled">SMTP Email Enabled <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="By default emails are sent with the underlying Mail Transfer Agent (MTA) installed on the server. If one is not installed emails will not be sent. To use a SMTP server instead check box and complete the fields below." data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					</div>
					<label class="form-label" for="smtpHost">SMTP Hostname/IP:</label>
					<div class="mb-3 input-group-sm w-75">
						<input type="text" class="form-control shadow smtpupdate" id="smtpHost" value="{$smtpSettings['smtp-hostname']}" placeholder="smtp.demo.local">
					</div>
					<label class="form-label" for="smtpPort">SMTP Port:</label>
					<div class="mb-3 input-group-sm w-75">
						<input type="text" class="form-control shadow smtpupdate" id="smtpPort" value="{$smtpSettings['smtp-port']}" placeholder="e.g. 25">
					</div>
					<label class="form-label" for="smtpUsername">SMTP Username:</label>
					<div class="mb-3 input-group-sm w-75">
						<input type="text" class="form-control shadow smtpupdate" id="smtpUsername" value="{$smtpSettings['smtp-username']}">
					</div>
					<label class="form-label" for="smtpPassword">SMTP Pasword:</label>
					<div class="mb-3 input-group-sm w-75">
						<input type="password" class="form-control shadow" id="smtpPassword">
					</div>
					<label class="form-label" for="smtpEncryption">SMTP Encryption:</label>
					<div class="mb-3 input-group-sm w-75">
						<select class="form-select form-select-sm shadow smtpupdate" id="smtpEncryption">
							<option value="None"{$smtpSettings['encryption-none']}>None</option>
							<option value="TLS"{$smtpSettings['encryption-tls']}>TLS</option>
							<option value="STARTTLS"{$smtpSettings['encryption-starttls']}>STARTTLS</option>
						</select>
					</div>
				</div>
				<div class="card-footer">
					<button id="updatesmtp" module="sysconfig" sub-module="update" module-action="smtpupdate" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
					<button id="setsmtppass" module="sysconfig" sub-module="update" module-action="smtppass" type="submit" class="btn btn-primary btn-sm shadow" disabled>Set Password</button>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
?>