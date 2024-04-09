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
	
	$associationList = $ipskISEDB->getEndPointAssociations();
	$pageStart = 0;
	$pageEnd = $associationList['count'];
		
	if($associationList){
		if($associationList['count'] > 0){

			$pageData['endpointAssociationList'] .= '<table id="endpoint-table" class="table table-hover"><thead><tr id="endpoint-table-filter"><th scope="col">MAC Address</th><th scope="col">iPSK Endpoint Grouping</th><th scope="col">Expiration Date</th><th style="display:none;">Full Name</th><th style="display:none;">Email</th><th style="display:none;">Description</th><th scope="col">View</th><th scope="col">Actions</th></tr><tr id="endpoint-table-header"><th scope="col">MAC Address</th><th scope="col">iPSK Endpoint Grouping</th><th scope="col">Expiration Date</th><th style="display:none;">Full Name</th><th style="display:none;">Email</th><th style="display:none;">Description</th><th scope="col">View</th><th scope="col">Actions</th></tr></thead><tbody>';
			
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
	<div class="col"><hr></div>
</div>
<div class="row menubar">
	<div class="col-2"><a id="newEndpoint" module="endpoints" sub-module="add" class="btn btn-primary nav-link custom-link text-white" href="#" role="button">Add Endpoint</a></div>
	<div class="col-3"><a id="bulkEndpoint" module="endpoints" sub-module="bulk" class="btn btn-primary nav-link custom-link text-white" href="#" role="button">Add Bulk Endpoints</a></div>
	<div class="col-7"></div>
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

	$(document).ready( function makeDataTable() {
		$('#endpoint-table thead #endpoint-table-filter th').each( function () {
        var title = $('#endpoint-table thead #endpoint-table-header th').eq( $(this).index() ).text();
		if (/^(View|Actions)$/.test(title)) {
			$(this).html('&nbsp;');
		} else {
			$(this).html('<input type="text" placeholder="Filter '+title+'" />');
		}
    	} );

		$("input[placeholder]").each(function () {
        	$(this).attr('size', $(this).attr('placeholder').length);
    	});
		
		$("#endpoint-table").DataTable({
			"paging": true,
			"stateSave": true,
			"lengthMenu": [ [15, 30, 45, 60, -1], [15, 30, 45, 60, "All"] ],
			"stateLoadParams": function(settings, data) {
  				for (i = 0; i < data.columns["length"]; i++) {
    				var col_search_val = data.columns[i].search.search;
    				if (col_search_val != "") {
      					$("input", $("#endpoint-table thead #endpoint-table-filter th")[i]).val(col_search_val);
    				}
  				}
			}
		});

		var table = $("#endpoint-table").DataTable();
		$("#endpoint-table thead #endpoint-table-filter input").on( 'keyup change', function () {
        table
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();
    } );


	} );

</script>