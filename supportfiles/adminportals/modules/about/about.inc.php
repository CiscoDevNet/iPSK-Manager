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
	if (extension_loaded('xml')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'xml'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'xml'</strong> is NOT Installed</div>";}
	if (extension_loaded('sodium')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'sodium'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'sodium'</strong> is NOT Installed</div>";}
	if (extension_loaded('json')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'json'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'json'</strong> is NOT Installed</div>";}

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
	<div class="col-6 text-center"><h5>License Information</h5><a target="new" href="http://www.apache.org/licenses/LICENSE-2.0">http://www.apache.org/licenses/LICENSE-2.0</a>
	<textarea class="form-control" style="min-width: 100%; min-height: 450px;" readonly>
     Copyright 2021 Cisco Systems, Inc. or its affiliates

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
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