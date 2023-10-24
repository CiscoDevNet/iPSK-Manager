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
	
	$actionRowData = "";
	$pageData['endpointAssociationList'] = '';
	$pageData['pageinationOutput'] = '';
	$totalPages = 0;
	$currentPage = 0;
	$currentPageSizeSelection = "";
	
	$pageSize = (isset($_GET['pageSize'])) ? $_GET['pageSize'] : 25;
	$currentPage = (isset($_GET['currentPage'])) ? $_GET['currentPage'] : 1;
	
	$associationList = $ipskISEDB->getEndPointAssociations();
	$pageEnd = $associationList['count'];
		
	if($associationList){
		if($associationList['count'] > 0){

			$pageData['endpointAssociationList'] .= '<table id="endpoint-table" class="table table-hover"><thead><tr><th scope="col">MAC Address</th><th scope="col">iPSK Endpoint Grouping</th><th scope="col">Expiration Date</th><th style="display:none;">Full Name</th><th style="display:none;">Email</th><th style="display:none;">Description</th><th scope="col">View</th><th scope="col">Actions</th></tr></thead><tbody>';
			
			for($idxId = $pageStart; $idxId < $pageEnd; $idxId++) {
							
				if($associationList[$idxId]['accountEnabled'] == 1){
					if($associationList[$idxId]['expirationDate'] == 0){
						$expiration = "Never";
					}elseif($associationList[$idxId]['expirationDate'] < time()){
						$expiration = '<span class="text-danger">Expired</span>';
					}else{
						$expiration = date($globalDateOutputFormat,$associationList[$idxId]['expirationDate']);
					}
				}else{
					$expiration = "Suspended";
				}

				// Skips adding the row to the table if the MAC address is empty.
				if ($associationList[$idxId]['macAddress'] == "") {
					continue;
				}

				$pageData['endpointAssociationList'] .= '<tr>';
				$pageData['endpointAssociationList'] .= '<td>'.$associationList[$idxId]['macAddress'].'</td>';
				$pageData['endpointAssociationList'] .= '<td>'.$associationList[$idxId]['groupName'].'</td>';
				$pageData['endpointAssociationList'] .= '<td>'.$expiration.'</td>';
				$pageData['endpointAssociationList'] .= '<td style="display:none;">'.$associationList[$idxId]['fullName'].'</td>';
				$pageData['endpointAssociationList'] .= '<td style="display:none;">'.$associationList[$idxId]['email'].'</td>';
				$pageData['endpointAssociationList'] .= '<td style="display:none;">'.$associationList[$idxId]['description'].'</td>';
				$pageData['endpointAssociationList'] .= '<td><a class="epg-tableicons" module="endpoints" sub-module="view" row-id="'.$associationList[$idxId]['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';

				
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="suspend" row-id="'.$associationList[$idxId]['id'].'" href="#">Suspend</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="activate" row-id="'.$associationList[$idxId]['id'].'" href="#">Activate</a>';	
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="extend" row-id="'.$associationList[$idxId]['id'].'" href="#">Extend</a>';	
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="edit" row-id="'.$associationList[$idxId]['id'].'" href="#">Edit</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="delete" row-id="'.$associationList[$idxId]['id'].'" href="#">Delete</a>';
				
				$pageData['endpointAssociationList'] .= '<td><div class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><span data-feather="more-vertical"></span></a><div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'.$actionRowData.'</div></div></td>';	
				
				$actionRowData = "";
				
				$pageData['endpointAssociationList'] .= '</tr>';
			}
			
			$pageData['endpointAssociationList'] .= "</tbody></table>";
		}
	}

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
	<div class="col-2"><a id="newEndpoint" module="endpoints" sub-module="add" class="nav-link custom-link" href="#"><span data-feather="plus-circle"></span>Add Endpoint</a></div>
	<div class="col-2"><a id="bulkEndpoint" module="endpoints" sub-module="bulk" class="nav-link custom-link" href="#"><span data-feather="plus-circle"></span>Add Bulk Endpoints</a></div>
	<div class="col-8"></div>
</div>
<div class="row">
	<div class="col">
		<hr>
	</div>
</div>
<div class="overflow-auto"><?php print $pageData['endpointAssociationList'];?></div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div id="popupcontent"></div>


<!-- Javascript DataTables -->
<link href="//cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
<script type="text/javascript" src="/scripts/jquery.dataTables.min.js"></script>

<script>
	$(function() {	
		feather.replace()
	});
	
	$(".action-pageicons").click(function(event) {
		
		$.ajax({
			url: "ajax/getmodule.php?pageSize=" + $("#pageSize").val() + "&currentPage=" + $(this).attr("page"),
			
			data: {
				module: $(this).attr('module')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#mainContent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			}
		});
		
		event.preventDefault();
	
	});
	
	$("#pageSize").change(function() {
		
		$.ajax({
			url: "ajax/getmodule.php?pageSize=" + $(this).val(),
			
			data: {
				module: $(this).attr('module')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#mainContent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			}
		});
		
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

	$(document).ready( function makeDataTable() {
		$("#endpoint-table").DataTable({
			"paging": true,
			"lengthMenu": [ [15, 30, 45, 60, -1], [15, 30, 45, 60, "All"] ]
		});
	} );

</script>