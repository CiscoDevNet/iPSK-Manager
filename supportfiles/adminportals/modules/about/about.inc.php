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
	
/*	AUTHOR(s):	Gary Oppel (gaoppel@cisco.com)
				Hosuk Won (howon@cisco.com)
	CONTRIBUTOR(s): Drew Betz (anbetz@cisco.com)
	*/
	
	$platformDetails = "";
	
	if (extension_loaded('mbstring')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'mbstring'</strong> Installed</div>"; }else{ $platformDetails .=  "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'mbstring'</strong> is NOT Installed</div>"; }
	if (extension_loaded('ldap')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'ldap'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'ldap'</strong> is NOT Installed</div>";}
	if (extension_loaded('mysqli')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'mysqli'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'mysqli'</strong> is NOT Installed</div>";}
	if (extension_loaded('mysqlnd')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'mysqlng'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'mysqlng'</strong> is NOT Installed</div>";}
	if (extension_loaded('curl')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'curl'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'curl'</strong> is NOT Installed</div>";} 
	if (extension_loaded('simplexml')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'simplexml'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'simplexml'</strong> is NOT Installed</div>";}


	$platformDetails .= "<div>iPSK Manager Database Scheme Version: <strong>".$ipskISEDB->get_dbSchemaVersion()."</strong></div>";

?>
<div class="row">
	<div class="col-12"><h1 class="text-center">About</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center"></div>
</div>
<div class="row">
	<div class="col-12"></div>
</div>
</div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div class="row">
	<div class="col-6 text-center"><h5>License Information</h5><a target="new" href="https://developer.cisco.com/docs/licenses">https://developer.cisco.com/docs/licenses</a>
	<textarea class="form-control" style="min-width: 100%; min-height: 450px;" readonly>
	Copyright (c) 2019 Cisco and/or its affiliates.

This software is licensed to you under the terms of the Cisco Sample
Code License, Version 1.1 (the "License"). You may obtain a copy of the
License at

			   https://developer.cisco.com/docs/licenses

All use of the material herein must be in accordance with the terms of
the License. All rights not expressly granted by the License are
reserved. Unless required by applicable law or agreed to separately in
writing, software distributed under the License is distributed on an "AS
IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
or implied.

AUTHOR(s):	Gary Oppel (gaoppel@cisco.com)
			Hosuk Won (howon@cisco.com)
CONTRIBUTOR(s): Drew Betz (anbetz@cisco.com)
	</textarea>
	</div>
	<div class="col-6"><h5 class="text-center">Platform Details</h5>
	<?php print $platformDetails; ?>

	</div>
</div>

<script>
	$(function() {	
		feather.replace()
	});
</script>