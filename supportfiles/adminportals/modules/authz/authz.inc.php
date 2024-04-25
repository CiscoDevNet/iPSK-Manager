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

	$authorizationTemplates = $ipskISEDB->getAuthorizationTemplates();
?>

<div class="row">
	<div class="col-12"><h1 class="text-center">Authorization Templates</h1></div>
</div>
<div class="row">
	<div class="col-12"><h5 class="h5 text-center">Manage iPSK Authorization Profiles to Add, View, Edit, and/or Delete</h5><div class="text-center">Authorization Templates are applied to an Endpoint when they are added to the iPSK Management Database.  These settings do not reflect the Policies created within Cisco ISE by the Administrator.</div></div>
</div>
</div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-3"><a id="addAuthZTemp" module="authz" sub-module="add" class="btn btn-primary custom-link text-white" href="#" role="button">Add Authorization Template</a></div>
	<div class="col-9"></div>
</div>
<div class="row">
	<div class="col">
		<br />
	</div>
</div>
<table class="table table-hover">
  <thead>
    <tr>
      <th scope="col">Profile Name</th>
      <th scope="col">Description</th>
      <th scope="col">iPSK Type</th>
	  <th scope="col">View</th>
	  <th scope="col">Edit</th>
	  <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($authorizationTemplates){
			while($row = $authorizationTemplates->fetch_assoc()) {
				if($row['ciscoAVPairPSKMode'] == "ascii"){
					if($row['ciscoAVPairPSK'] == "*userrandom*"){
						$pskType = "Unique User PSK";
					}elseif($row['ciscoAVPairPSK'] == "*devicerandom*"){
						$pskType = "Unique Device PSK";
					}else{
						$pskType = "Common PSK";
					}
				}else{
					$pskType = "None";
				}
				
				print '<tr>';
				print '<td>'.$row['authzPolicyName'].'</td>';
				print '<td>'.$row['authzPolicyDescription'].'</td>';
				print '<td>'.$pskType.'</td>';
				print '<td><a class="epg-tableicons" module="authz" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';
				print '<td><a class="epg-tableicons" module="authz" sub-module="edit" row-id="'.$row['id'].'" href="#"><span data-feather="edit"></span></a></td>';
				print '<td><a class="epg-tableicons" module="authz" sub-module="delete" row-id="'.$row['id'].'" href="#"><span data-feather="x-square"></span></a></td>';
				print '</tr>';
				
				$pskType = "";
			}
		}
	?>
  </tbody>
</table>
<div id="popupcontent"></div>
<script>
	$(function() {	
		feather.replace()
	});
	
	$(".epg-tableicons").click(function(event) {
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $(this).attr('row-id')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
		
		event.preventDefault();
	});
	
	$(".custom-link").click(function(event) {
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
		
		event.preventDefault();
	});
</script>