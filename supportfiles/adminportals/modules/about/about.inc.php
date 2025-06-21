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

	$platformDetails .= "<div>iPSK Manager Database Schema Version: <strong>".$ipskISEDB->get_dbSchemaVersion()."</strong></div>";

?>

    <div class="row row-cols-1 row-cols-md-2 g-4">
      <div class="col">
        <div class="card h-100">
		<div class="card-header bg-primary text-white">License Information</div>
          <div class="card-body">
            <p class="card-text">
              Copyright 2025 Cisco Systems, Inc. or its affiliates
              <br>
              <br>
              Licensed under the Apache License, Version 2.0 (the "License");
              you may not use this file except in compliance with the License.
              You may obtain a copy of the License at
              <a href="http://www.apache.org/licenses/LICENSE-2.0">http://www.apache.org/licenses/LICENSE-2.0</a>
              <br>
              <br>
              Unless required by applicable law or agreed to in writing, software
              distributed under the License is distributed on an "AS IS" BASIS,
              WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
              See the License for the specific language governing permissions and
              limitations under the License.
            </p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card h-100">
		<div class="card-header bg-primary text-white">Platform Details</div>
          <div class="card-body">
            <p class="card-text">
			<?php print $platformDetails; ?>
            </p>
          </div>
        </div>
      </div>
    </div>


<script>
	$(function() {	
		feather.replace()
	});
</script>