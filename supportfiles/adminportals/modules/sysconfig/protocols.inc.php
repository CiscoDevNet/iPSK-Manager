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
		<div class="row text-center text-primary">
			<div class="col "><h5>Portal Protocols & Port Settings</h5></div>
		</div>
		<div class="form-group font-weight-bold">
			<label class="font-weight-bold" for="protocolPorts">Available Portal Protocols (Port):</label>		
			<select class="form-control shadow" id="protocolPorts" multiple>
				$portsAndProtocolsOutput
			</select>
		</div>
		<button id="deleteprotocol" module="sysconfig" sub-module="delete" module-action="protocol" type="submit" class="btn btn-primary shadow">Delete Selected</button>
		<div class="row">
			<div class="col"><hr></div>
		</div>
		<div class="row">
			<div class="col p-3">
				<div class="form-group font-weight-bold">
					<label class="font-weight-bold" for="protocol">Select Protocol:</label>		
					<select class="form-control shadow" id="protocol">
						<option value="0">HTTP</option>
						<option value="1" selected>HTTPS</option>
					</select>
				</div>
			</div>
			<div class="col p-3">
				<div class="form-group input-group-sm font-weight-bold">
					<label class="font-weight-bold" for="portalPort">TCP Port:</label>
					<input type="text" class="form-control shadow generaltab" id="portalPort" placeholder="e.g. 8443">
				</div>
			</div>
		</div>	
		<button id="addprotocol" module="sysconfig" sub-module="create" module-action="protocol" type="submit" class="btn btn-primary shadow">Add Protocol/Port</button>
	</div>
</div>
HTML;

?>
