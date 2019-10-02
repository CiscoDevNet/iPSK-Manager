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
	
	$actionRowData = "";
	
	$endPointAssociations = $ipskISEDB->getEndPointAssociations();
?>

<div class="row">
	<div class="col-12"><h1 class="text-center">Managed iPSK Endpoints</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center">Manage iPSK Endpoints to Add, View, Edit, and/or Delete</h6></div>
</div>
<div class="row">
	<div class="col-1 text-danger">Actions:</div>
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-2">No Actions Available<!--<a id="addEndpoint" module="endpoints" sub-module="add" class="nav-link custom-link" href="#"><span data-feather="plus-circle"></span>Add End Point</a>--></div>
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
      <th scope="col">MAC Address</th>
      <th scope="col">iPSK Endpoint Grouping</th>
      <th scope="col">Expiration Date</th>
	  <th scope="col">View</th>
	  <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($endPointAssociations){
			while($row = $endPointAssociations->fetch_assoc()) {
				if($row['accountEnabled'] == 1){
					if($row['expirationDate'] == 0){
						$expiration = "Never";
					}elseif($row['expirationDate'] < time()){
						$expiration = '<span class="text-danger">Expired</span>';
					}else{
						$expiration = date($globalDateOutputFormat,$row['expirationDate']);
					}
				}else{
					$expiration = 'Suspended';
				}
				
				print '<tr>';
				print '<td>'.$row['macAddress'].'</td>';
				print '<td>'.$row['epGroupName'].'</td>';
				print '<td>'.$expiration.'</td>';
				print '<td><a class="epg-tableicons" module="endpoints" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';


				
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="suspend" row-id="'.$row['id'].'" href="#">Suspend</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="activate" row-id="'.$row['id'].'" href="#">Activate</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="extend" row-id="'.$row['id'].'" href="#">Extend</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="edit" row-id="'.$row['id'].'" href="#">Edit</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="delete" row-id="'.$row['id'].'" href="#">Delete</a>';
				
				print '<td><div class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><span data-feather="more-vertical"></span></a><div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'.$actionRowData.'</div></div></td>';

				print '</tr>';
				$actionRowData = "";
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
			}
		});
		
		event.preventDefault();
	});
	
	$(".action-tableicons").click(function(event) {
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
				//alert("success");
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
			}
		});
		
		event.preventDefault();
	});
</script>