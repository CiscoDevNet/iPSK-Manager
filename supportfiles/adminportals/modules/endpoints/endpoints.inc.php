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
	$pageData['endpointAssociationList'] = '';
	$pageData['pageinationOutput'] = '';
	$totalPages = 0;
	$currentPage = 0;
	$currentPageSizeSelection = "";
	
	$pageSize = (isset($_GET['pageSize'])) ? $_GET['pageSize'] : 25;
	$currentPage = (isset($_GET['currentPage'])) ? $_GET['currentPage'] : 1;
	
	$associationList = $ipskISEDB->getEndPointAssociations();
		
	if($associationList){
		if($associationList['count'] > 0){
			$pageSizes = Array(25, 50, 75, 100);

			foreach($pageSizes as $entry){
				if($entry == $pageSize){
					$currentPageSizeSelection .= '<option value="'.$entry.'" selected>'.$entry.'</option>';
				}else{
					$currentPageSizeSelection .= '<option value="'.$entry.'">'.$entry.'</option>';
				}
			}
						
			$totalPages = ceil($associationList['count'] / $pageSize);
			
			if($currentPage > $totalPages){
				$currentPage = $totalPages;
			}
				
			$nextPage = $currentPage + 1;
			
			if($currentPage == 0 || $currentPage == 1){
				$currentPage = 1;
				
				$pageStart = 0;
				$pageEnd = $pageStart + $pageSize;
				
				if($pageEnd > $associationList['count']){
					$pageEnd = $associationList['count'];
				}
				
			}else{
				$pageStart = ($currentPage - 1) * $pageSize;
				$pageEnd = $pageStart + $pageSize;
				
				$previousPage = $currentPage - 1;
				
				$pageData['pageinationOutput'] .= '<a class="action-pageicons mx-1" module="endpoints" page="1" href="#"><span data-feather="chevrons-left"></span></a>';
				$pageData['pageinationOutput'] .= '<a class="action-pageicons mx-1" module="endpoints" page="'.$previousPage.'" href="#"><span data-feather="chevron-left"></span></a>';		
				
				if($pageEnd > $associationList['count']){
					$pageEnd = $associationList['count'];
				}
			}
			
			$pageData['pageinationOutput'] .= "<strong>".$currentPage."</strong>";
			
			if($currentPage != $totalPages && $totalPages != 0){
				$pageData['pageinationOutput'] .= '<a class="action-pageicons mx-1" module="endpoints" page="'.$nextPage.'" href="#"><span data-feather="chevron-right"></span></a>';
				$pageData['pageinationOutput'] .= '<a class="action-pageicons mx-1" module="endpoints" page="'.$totalPages.'" href="#"><span data-feather="chevrons-right"></span></a>';
			}
			
			$pageData['endpointAssociationList'] .= '<table class="table table-hover"><thead><tr><th scope="col">MAC Address</th><th scope="col">iPSK Endpoint Grouping</th><th scope="col">Expiration Date</th><th scope="col">View</th><th scope="col">Actions</th></tr></thead><tbody>';
			
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

				$pageData['endpointAssociationList'] .= '<tr>';
				$pageData['endpointAssociationList'] .= '<td>'.$associationList[$idxId]['macAddress'].'</td>';
				$pageData['endpointAssociationList'] .= '<td>'.$associationList[$idxId]['groupName'].'</td>';
				$pageData['endpointAssociationList'] .= '<td>'.$expiration.'</td>';
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
	<div class="col-10"></div>
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
<div class="row">
	<div class="col-4">
		<label class="font-weight-bold" for="pageSize">Items per Page:</label>
		<select id="pageSize" module="endpoints"><?php print $currentPageSizeSelection;?></select>
	</div>
	<div class="col text-center"><strong>Total Items: (<?php print $associationList['count'];?>)  Total Pages: <?php print $totalPages;?></strong></div>
	<div class="col-4 text-right">
		<?php print $pageData['pageinationOutput'];?>
	</div>
</div>
<div id="popupcontent"></div>
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
</script>