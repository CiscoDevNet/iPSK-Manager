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
	


	$wirelessNetworks = $ipskISEDB->getWirelessNetworks();
?>

<div class="row">
	<div class="col-12"><h1 class="text-center">Wireless Networks</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center">Manage iPSK Wireless SSIDs to Add, View, Edit, and/or Delete</h6><div class="text-center">Wireless SSIDs are used in email communications when an endpoint is Enrolled in the iPSK Management Database.</div></div>
</div>
<div class="row">
	<div class="col-1 text-danger">Actions:</div>
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-2"><a id="addWirelessNetwork" module="wireless" sub-module="add" class="nav-link custom-link" href="#"><span data-feather="plus-circle"></span>Add Wireless Network</a></div>
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
      <th scope="col">Wireless Network SSID</th>
      <th scope="col">Wireless Network Description</th>
	  <th scope="col">View</th>
	  <th scope="col">Edit</th>
	  <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($wirelessNetworks){
			while($row = $wirelessNetworks->fetch_assoc()) {				
				print '<tr>';
				print '<td>'.$row['ssidName'].'</td>';
				print '<td>'.$row['ssidDescription'].'</td>';
				print '<td><a class="epg-tableicons" module="wireless" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';
				print '<td><a class="epg-tableicons" module="wireless" sub-module="edit" row-id="'.$row['id'].'" href="#"><span data-feather="edit"></span></a></td>';
				print '<td><a class="epg-tableicons" module="wireless" sub-module="delete" row-id="'.$row['id'].'" href="#"><span data-feather="x-square"></span></a></td>';
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