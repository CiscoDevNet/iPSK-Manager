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
	


	$portals = $ipskISEDB->getPortals();
	$directoryNames = $ipskISEDB->getAuthDirectoryNames();
	
?><div class="row">
	<div class="col-12"><h1 class="text-center">Portals</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center">Manage iPSK Portals Add, View, Edit, and/or Delete</h6></div>
</div>
</div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-2"><a id="addSponsor" module="portals" sub-module="add" class="btn btn-primary nav-link custom-link text-white" href="#" role="button">Add Portal</a></div>
	<div class="col-11"></div>
</div>
<div class="row">
	<div class="col">
		<br />
	</div>
</div>
<table class="table table-hover">
  <thead>
    <tr>
      <th scope="col">Portal Name</th>
      <th scope="col">Description</th>
      <th scope="col">Portal Hostname</th>
	  <th scope="col">Authentication Directory</th>
	  <th scope="col">View</th>
	  <th scope="col">Edit</th>
	  <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($portals){
			while($row = $portals->fetch_assoc()) {		
				print '<tr>';
				print '<td>'.$row['portalName'].'</td>';
				print '<td>'.$row['description'].'</td>';
				print '<td>'.$row['portalHostname'].'</td>';
				if(isset($directoryNames[$row['authenticationDirectory']])){
					print '<td>'.$directoryNames[$row['authenticationDirectory']].'</td>';
				}else{
					print '<td>{UNKNOWN}</td>';
				}
				print '<td><a class="epg-tableicons" module="portals" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';
				print '<td><a class="epg-tableicons" module="portals" sub-module="edit" row-id="'.$row['id'].'" href="#"><span data-feather="edit"></span></a></td>';
				print '<td><a class="epg-tableicons" module="portals" sub-module="delete" row-id="'.$row['id'].'" href="#"><span data-feather="x-square"></span></a></td>';
				print '</tr>';
				
				//$pskType = "";
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