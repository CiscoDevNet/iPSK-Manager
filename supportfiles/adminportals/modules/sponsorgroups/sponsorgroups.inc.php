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
	


	$endSponsorGroups = $ipskISEDB->getSponsorGroups();
?>

<div class="row">
	<div class="col-12"><h1 class="text-center">Portal Groups</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center">Portal Groups are groups used to define permisssions per endpoint and/or group.</h6></div>
</div>
<div class="row">
	<div class="col-1 text-danger">Actions:</div>
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-2"><a id="addPortalGroup" module="sponsorgroups" sub-module="add" class="nav-link custom-link" href="#"><span data-feather="plus-circle"></span>Add Portal Group</a></div>
	<div class="col-11"></div>
</div>
<div class="row">
	<div class="col">
		<hr>
	</div>
</div>
<table class="table table-hover">
  <thead>
    <tr>
      <th scope="col">Portal Group Name</th>
      <th scope="col">Portal Group Description</th>
	  <th scope="col">Portal Group Authentication Type</th>
	  <th scope="col">Portal Group Type</th>
	  <th scope="col">Max Devices per User</th>
	  <th scope="col">View</th>
	  <th scope="col">Edit</th>
	  <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($endSponsorGroups){
			while($row = $endSponsorGroups->fetch_assoc()) {
				if($row['sponsorGroupAuthType'] == 0){
					$groupAuthType = "Internal Authentication";
				}else{
					$groupAuthType = "External Authentication";
				}
				
				if($row['sponsorGroupType'] == 0){
					$groupType = "Sponsor";
				}else{
					$groupType = "Non-Sponsor";
				}
				
				if($row['maxDevices'] == 0){
					$deviceCount = "Unlimited";
				}else{
					$deviceCount = $row['maxDevices'];
				}
				
				print '<tr>';
				print '<td>'.$row['sponsorGroupName'].'</td>';
				print '<td>'.$row['sponsorGroupDescription'].'</td>';
				print '<td>'.$groupAuthType.'</td>';
				print '<td>'.$groupType.'</td>';
				print '<td>'.$deviceCount.'</td>';
				print '<td><a class="epg-tableicons" module="sponsorgroups" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a>';
				print '<td><a class="epg-tableicons" module="sponsorgroups" sub-module="edit" row-id="'.$row['id'].'" href="#"><span data-feather="edit"></span></a>';
				print '<td><a class="epg-tableicons" module="sponsorgroups" sub-module="delete" row-id="'.$row['id'].'" href="#"><span data-feather="x-square"></span></a></td>';
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