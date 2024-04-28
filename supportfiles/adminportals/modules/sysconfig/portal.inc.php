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
          		<div class="card-header bg-primary text-white">Portal Hostnames</div>
          		<div class="card-body">
					<div class="mb-3">
						<label class="form-label" for="portalHostname">Portal Hostnames:</label>		
						<select class="form-select form-select-sm shadow" id="portalHostname" multiple>
							$hostnameOutput
						</select>
					</div>
						<div class="mb-3 w-50">
							<label class="form-label" for="hostname">Hostname:</label>
							<div class="mb-3 input-group-sm">
								<input type="text" class="form-control shadow" id="hostname" placeholder="sample.domain.com">
							</div>
						</div>
					
				</div>
				<div class="card-footer">
					<button id="deletehostname" module="sysconfig" sub-module="delete" module-action="hostname" type="submit" class="btn btn-primary btn-sm shadow" disabled>Delete Selected</button>
					<button id="addhostname" module="sysconfig" sub-module="create" module-action="hostname" type="submit" class="btn btn-primary btn-sm shadow" disabled>Add New Hostname</button>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card h-100">
          		<div class="card-header bg-primary text-white">Portal Protocols & Port Settings</div>
          		<div class="card-body">
					<div class="mb-3">
						<label class="form-label" for="protocolPorts">Available Portal Protocols (Port):</label>		
						<select class="form-select form-select-sm shadow" id="protocolPorts" multiple>
							$portsAndProtocolsOutput
						</select>
					</div>
					<div class="row">
						<div class="col mb-3">
							<label class="form-label" for="protocol">Select Protocol:</label>		
								<select class="form-select form-select-sm shadow" id="protocol">
									<option value="0">HTTP</option>
									<option value="1" selected>HTTPS</option>
								</select>
						</div>
						<div class="col input-group-sm mb-3">
							<label class="form-label" for="portalPort">TCP Port:</label>
							<input type="text" class="form-control
							 shadow" id="portalPort" placeholder="e.g. 8443">
						</div>
					</div>
				</div>
				<div class="card-footer">
					<button id="deleteprotocol" module="sysconfig" sub-module="delete" module-action="protocol" type="submit" class="btn btn-primary btn-sm shadow" disabled>Delete Selected</button>
					<button id="addprotocol" module="sysconfig" sub-module="create" module-action="protocol" type="submit" class="btn btn-primary btn-sm shadow" disabled>Add Protocol/Port</button>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
?>
