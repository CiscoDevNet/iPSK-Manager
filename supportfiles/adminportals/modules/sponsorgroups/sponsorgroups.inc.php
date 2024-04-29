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
	


	$endSponsorGroups = $ipskISEDB->getSponsorGroups();
?>
<div class="card">
	<h4 class="text-center card-header bg-primary text-white pb-0 border-bottom-0">Portal Groups</h4>
	<h6 class="text-center card-header bg-primary text-white pt-0 border-top-0 fst-italic">Groups used to define permisssions per endpoint and/or group</h6>
	<div class="card-header">
		<a id="addPortalGroup" module="sponsorgroups" sub-module="add" class="btn btn-primary custom-link text-white" href="#" role="button">Add Portal Group</a>
	</div>
	<div class="card-body">
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
	</div>
</div>
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