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

			$pageData['endpointAssociationList'] .= '<table id="endpoint-table" class="table table-hover"><thead><tr id="endpoint-table-filter"><th scope="col" data-dt-order="disable">MAC Address</th><th scope="col" data-dt-order="disable">iPSK Endpoint Grouping</th><th scope="col" data-dt-order="disable">Expiration Date</th><th scope="col" data-dt-order="disable">Full Name</th><th scope="col" data-dt-order="disable">Email</th><th scope="col" data-dt-order="disable">Description</th><th scope="col">View</th><th scope="col">Actions</th></tr><tr id="endpoint-table-header"><th scope="col">MAC Address</th><th scope="col">iPSK Endpoint Grouping</th><th scope="col">Expiration Date</th><th scope="col">Full Name</th><th scope="col">Email</th><th scope="col">Description</th><th scope="col">View</th><th scope="col">Actions</th></tr></thead><tbody>';
			
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
				$pageData['endpointAssociationList'] .= '<td>'.$associationList[$idxId]['fullName'].'</td>';
				$pageData['endpointAssociationList'] .= '<td>'.$associationList[$idxId]['email'].'</td>';
				$pageData['endpointAssociationList'] .= '<td>'.$associationList[$idxId]['description'].'</td>';
				$pageData['endpointAssociationList'] .= '<td><a class="epg-tableicons" module="endpoints" sub-module="view" row-id="'.$associationList[$idxId]['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';

				
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="suspend" row-id="'.$associationList[$idxId]['id'].'" href="#">Suspend</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="activate" row-id="'.$associationList[$idxId]['id'].'" href="#">Activate</a>';	
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="extend" row-id="'.$associationList[$idxId]['id'].'" href="#">Extend</a>';	
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="edit" row-id="'.$associationList[$idxId]['id'].'" href="#">Edit</a>';
				$actionRowData .= '<a class="dropdown-item action-tableicons" module="endpoints" sub-module="delete" row-id="'.$associationList[$idxId]['id'].'" href="#">Delete</a>';
				
				$pageData['endpointAssociationList'] .= '<td><div class="dropdown"><a class="dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#"><span data-feather="more-vertical"></span></a><div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'.$actionRowData.'</div></div></td>';	
				
				$actionRowData = "";
				
				$pageData['endpointAssociationList'] .= '</tr>';
			}
			
			$pageData['endpointAssociationList'] .= "</tbody></table>";
		}
	}

?>

<div class="card">
	<h4 class="text-center card-header bg-primary text-white pb-0 border-bottom-0">Managed iPSK Endpoints</h4>
	<h6 class="text-center card-header bg-primary text-white pt-0 border-top-0 fst-italic">Manage iPSK Endpoints to Add, View, Edit, and/or Delete</h6>
	<div class="card-header">
		<a id="newEndpoint" module="endpoints" sub-module="add" class="btn btn-primary custom-link text-white" href="#" role="button">Add Endpoint</a>
		<a id="bulkEndpoint" module="endpoints" sub-module="bulk" class="btn btn-primary custom-link text-white" href="#" role="button">Add Bulk Endpoints</a>
	</div>
	<div class="card-body">
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
			  <?php print $pageData['endpointAssociationList'];?>
  			</tbody>
		</table>
		<div id="popupcontent"></div>
	</div>
</div>
<style>
	button.buttons-colvis {
    	background: #0d6efd !important;
	}
</style>
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
			"columnDefs": [
        		{
            		target: 3,
            		visible: false,
        		},
        		{
            		target: 4,
            		visible: false
        		},
				{
            		target: 5,
            		visible: false
        		},
				{
            		target: 6,
            		orderable: false
        		},
				{
            		target: 7,
            		orderable: false
        		},
				{ responsivePriority: 1, targets: -1 },
        		{ responsivePriority: 2, targets: -2 },
    		],
			layout: {
        		bottomStart: {
            		buttons: ['colvis']
        		}
    		},
			"paging": true,
			"responsive": true,
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