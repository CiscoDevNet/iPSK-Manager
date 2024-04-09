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
	


	$internalUsers = $ipskISEDB->getInternalUsers();
?>

<div class="row">
	<div class="col-12"><h1 class="text-center">Internal Identity Management - Users</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center">Manage iPSK Internal Users and Group Membership</h6></div>
</div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-2"><a id="addGroup" module="internalusers" sub-module="add" class="btn btn-primary nav-link custom-link text-white" href="#" role="button">Add User</a></div>
	<div class="col"></div>
</div>
<div class="row">
	<div class="col">
		<br/>
	</div>
</div>
<table class="table table-hover">
  <thead>
    <tr>
      <th scope="col">Username</th>
	  <th scope="col">Full Name</th>
      <th scope="col">Description</th>
	  <th scope="col">Manage Group Membership</th>
	  <th scope="col">Reset Password</th>
	  <th scope="col">View</th>
	  <th scope="col">Edit</th>
	  <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($internalUsers){
			while($row = $internalUsers->fetch_assoc()) {
							
				print '<tr>';
				print '<td>'.$row['userName'].'</td>';
				print '<td>'.$row['fullName'].'</td>';
				print '<td>'.$row['description'].'</td>';
				if($row['id'] != 1){
					print '<td><a class="epg-tableicons" module="internalusers" sub-module="groups" row-id="'.$row['id'].'" href="#"><span data-feather="users"></span></a></td>';
					print '<td><a class="epg-tableicons" module="internalusers" sub-module="pass" row-id="'.$row['id'].'" href="#"><span data-feather="lock"></span></a></td>';
					print '<td><a class="epg-tableicons" module="internalusers" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';
					print '<td><a class="epg-tableicons" module="internalusers" sub-module="edit" row-id="'.$row['id'].'" href="#"><span data-feather="edit"></span></a></td>';
					print '<td><a class="epg-tableicons" module="internalusers" sub-module="delete" row-id="'.$row['id'].'" href="#"><span data-feather="x-square"></span></a></td>';
				}else{
					print '<td></td>';
					print '<td><a class="epg-tableicons" module="internalusers" sub-module="pass" row-id="'.$row['id'].'" href="#"><span data-feather="lock"></span></a></td>';
					print '<td><a class="epg-tableicons" module="internalusers" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';
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