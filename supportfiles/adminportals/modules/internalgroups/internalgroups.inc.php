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
	


	$internalGroups = $ipskISEDB->getInternalGroups();
?>

<div class="row">
	<div class="col-12"><h1 class="text-center">Internal Identity Management - Groups</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center">Manage iPSK Internal Groups and map to External LDAP Groups</h6></div>
</div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-2"><a id="addGroup" module="internalgroups" sub-module="add" class="btn btn-primary nav-link custom-link text-white" href="#" role="button">Add Group</a></div>
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
      <th scope="col">Group Name</th>
      <th scope="col">Description</th>
	  <th scope="col">Group Type</th>
	  <th scope="col">Admin Portal</th>
	  <th scope="col">View</th>
	  <th scope="col">Edit</th>
	  <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($internalGroups){
			while($row = $internalGroups->fetch_assoc()) {
				if($row['groupType'] == '0'){
					$groupType = "Internal";
				}else{
					$groupType = "External";
				}
				
				if($row['permissions'] == '1'){
					$adminPortal = "check-square";
				}else{
					$adminPortal = "square";
				}
				
				print '<tr>';
				print '<td>'.$row['groupName'].'</td>';
				print '<td>'.$row['description'].'</td>';
				print '<td>'.$groupType.'</td>';
				print '<td><span class="epg-tableicons" data-feather="'.$adminPortal.'"></span></td>';
				print '<td><a class="epg-tableicons" module="internalgroups" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';
				if($row['id'] != 1){
					print '<td><a class="epg-tableicons" module="internalgroups" sub-module="edit" row-id="'.$row['id'].'" href="#"><span data-feather="edit"></span></a></td>';
					print '<td><a class="epg-tableicons" module="internalgroups" sub-module="delete" row-id="'.$row['id'].'" href="#"><span data-feather="x-square"></span></a></td>';
				}else{
					print '<td></td>';
					print '<td></td>';
				}
				print '</tr>';
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