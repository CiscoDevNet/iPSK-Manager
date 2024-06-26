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
          		<div class="card-header bg-primary text-white">Cisco ISE ERS Integration Settings</div>
          		<div class="card-body">
					<div class="mb-3 input-group-sm w-75">
						<label class="form-label" for="ersHost">Cisco ISE ERS Integration URL:</label>
						<input type="text" class="form-control shadow iseers" id="ersHost" value="{$iseERSSettings['ersHost']}" placeholder="https://ise.demo.local:9060">
					</div>
					<div class="mb-3 input-group-sm w-75">
						<label class="form-label" for="ersUsername">Cisco ISE ERS Integration Username:</label>
						<input type="text" class="form-control shadow iseers" id="ersUsername" value="{$iseERSSettings['ersUsername']}">
					</div>
					<div class="mb-3 input-group-sm w-75">
						<label class="form-label" for="ersPassword">Cisco ISE ERS Integration Pasword:</label>
						<input type="password" class="form-control shadow" id="ersPassword" value="">
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update iseers" base-value="1" value="{$iseERSSettings['verify-ssl-peer']}" id="ersVerifySsl"{$iseERSSettings['verify-ssl-peer-check']}>
						<label class="form-check-label" for="ersVerifySsl">Verify SSL Peer</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update iseers" base-value="1" value="{$iseERSSettings['enabled']}" id="ersEnabled"{$iseERSSettings['enabled-check']}>
						<label class="form-check-label" for="ersEnabled">Cisco ISE ERS Integration Enabled</label>
					</div>
				</div>
				<div class="card-footer">
					<button id="updateersise" module="sysconfig" sub-module="update" module-action="ersupdate" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
					<button id="seterspass" module="sysconfig" sub-module="update" module-action="erspass" type="submit" class="btn btn-primary btn-sm shadow" disabled>Set Password</button>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">Cisco ISE Monitoring Integration Settings</div>
          		<div class="card-body">
					<div class="mb-3 input-group-sm w-75">
						<label class="form-label" for="mntHostPrimary">Cisco ISE MnT Integration URL:</label>
						<input type="text" class="form-control shadow isemnt" id="mntHostPrimary" value="{$iseMNTSettings['mntHost']}" placeholder="https://ise.demo.local">
					</div>
					<div class="mb-3 input-group-sm w-75 d-none">
						<label class="form-label" for="mntHostSecondary">Cisco ISE MnT Integration Secondary Node Hostname:</label>
						<input type="text" class="form-control shadow isemnt" id="mntHostSecondary" value="">
					</div>
					<div class="mb-3 input-group-sm w-75">
						<label class="form-label" for="mntUsername">Cisco ISE MnT Integration Username:</label>
						<input type="text" class="form-control shadow isemnt" id="mntUsername" value="{$iseMNTSettings['mntUsername']}">
					</div>
					<div class="mb-3 input-group-sm w-75">
						<label class="form-label" for="mntPassword">Cisco ISE MnT Integration Pasword:</label>
						<input type="password" class="form-control shadow" id="mntPassword" value="">
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update isemnt" base-value="1" value="{$iseMNTSettings['verify-ssl-peer']}" id="mntVerifySsl"{$iseMNTSettings['verify-ssl-peer-check']}>
						<label class="form-check-label" for="mntVerifySsl">Verify SSL Peer</label>
					</div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input checkbox-update isemnt" base-value="1" value="{$iseMNTSettings['enabled']}" id="mntEnabled"{$iseMNTSettings['enabled-check']}>
						<label class="form-check-label" for="mntEnabled">Cisco ISE MnT Integration Enabled</label>
					</div>
				</div>
				<div class="card-footer">
					<button id="updatemntise" module="sysconfig" sub-module="update" module-action="mntupdate" type="submit" class="btn btn-primary btn-sm shadow" disabled>Update Settings</button>
					<button id="setmntpass" module="sysconfig" sub-module="update" module-action="mntpass" type="submit" class="btn btn-primary btn-sm shadow" disabled>Set Password</button>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
?>