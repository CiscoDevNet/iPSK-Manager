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
		<div class="row text-center text-primary">
			<div class="col "><h5>Portal Hostnames</h5></div>
		</div>
		<div class="form-group font-weight-bold">
			<label class="font-weight-bold" for="portalHostname">Portal Hostnames:</label>		
			<select class="form-control shadow" id="portalHostname" multiple>
				$hostnameOutput
			</select>
		</div>
		<button id="deletehostname" module="sysconfig" sub-module="delete" module-action="hostname" type="submit" class="btn btn-primary shadow">Delete Selected</button>
		<div class="row">
			<div class="col"><hr></div>
		</div>
		<div class="row">
			<div class="col p-3">
			<label class="font-weight-bold" for="hostname">Hostname:</label>
			<div class="form-group input-group-sm font-weight-bold">
				<input type="text" class="form-control shadow generaltab" id="hostname" placeholder="sample.domain.com">
			</div>
			</div>
		</div>	
		<button id="addhostname" module="sysconfig" sub-module="create" module-action="hostname" type="submit" class="btn btn-primary shadow">Add New Hostname</button>
	</div>
</div>
HTML;

?>
