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
	


	
?>
<div class="card">
	<h4 class="text-center card-header bg-primary text-white">System Logging</h4>
	<div class="card-header">
		<ul class="nav nav-pills card-header-pills">
        	<li class="nav-item">
        		<a class="nav-item nav-link active" id="nav-last-hour-tab" data-bs-toggle="tab" module="logging" value="60" href="#nav-last-hour" role="tab" aria-controls="nav-last-hour" aria-selected="true">Last Hour</a>
          	</li>
          	<li class="nav-item">
            	<a class="nav-item nav-link" id="nav-last-day-tab" data-bs-toggle="tab" module="logging" value="1440" href="#nav-last-day" role="tab" aria-controls="nav-last-day" aria-selected="false">Last Day</a>
          	</li>
        	<li class="nav-item">
				<a class="nav-item nav-link" id="nav-last-week-tab" data-bs-toggle="tab" module="logging" value="10080" href="#nav-last-week" role="tab" aria-controls="nav-last-week" aria-selected="false">Last Week</a>
			</li>
        	<li class="nav-item">
            	<a class="nav-item nav-link" id="nav-last-month-tab" data-bs-toggle="tab" module="logging" value="44640" href="#nav-last-month" role="tab" aria-controls="nav-last-month" aria-selected="false">Last Month</a>
        	</li>
        	<li class="nav-item">
				<a class="nav-item nav-link" id="nav-last-year-tab" data-bs-toggle="tab" module="logging" value="525600" href="#nav-last-year" role="tab" aria-controls="nav-last-year" aria-selected="false">Last Year</a>
			</li>
        </ul>
	</div>
	<div class="card-body">
	
<?php
if (isset($sanitizedInput['logDisplay'])) {
	$logging = $ipskISEDB->getLogging((int)$sanitizedInput['logDisplay']);
} else {
	$logging = $ipskISEDB->getLogging();
}
?>

<table id="logging-table" class="table table-hover">
  <thead>
    <tr id="logging-table-filter">
      <th scope="col" data-dt-order="disable">Date</th>
      <th scope="col" data-dt-order="disable">Session ID</th>
	  <th scope="col" data-dt-order="disable">Filename</th>
	  <th scope="col" data-dt-order="disable">Message</th>
	  <th scope="col" data-dt-order="disable">Details</th>
    </tr>
	<tr id="loggin-table-header">
      <th scope="col">Date</th>
      <th scope="col">Session ID</th>
	  <th scope="col">Filename</th>
	  <th scope="col">Message</th>
	  <th scope="col">Details</th>
    </tr>
  </thead>
  <tbody>
    <?php
		if($logging){
			while($row = $logging->fetch_assoc()) {				
				print '<tr>';
				print '<td>'.$row['dateCreated'].'</td>';
				print '<td>'.$row['sessionID'].'</td>';
				print '<td>'.$row['fileName'].'</td>';
				print '<td>'.$row['message'].'</td>';
				print '<td><a class="epg-tableicons" module="logging" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="info"></span></a></td>';
				print '</tr>';
			}
		}
	?>
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
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
		
		event.preventDefault();
	});
	
	$("#nav-last-hour-tab, #nav-last-day-tab, #nav-last-week-tab, #nav-last-month-tab, #nav-last-year-tab").click(function(event) {
		var logValue = $(this).attr('value');

		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'logDisplay':  $(this).attr('value')
			//	'sub-module': $(this).attr('sub-module')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				if(data != 0){
					$('#mainContent').html(data);
					$("#nav-last-hour-tab, #nav-last-day-tab, #nav-last-week-tab, #nav-last-month-tab, #nav-last-year-tab").removeClass("active");
					if(logValue == 60) {
						$("#nav-last-hour-tab").addClass("active");
					}
					if(logValue == 1440) {
						$("#nav-last-day-tab").addClass("active");
					}
					if(logValue == 10080) {
						$("#nav-last-week-tab").addClass("active");
					}
					if(logValue == 44640) {
						$("#nav-last-month-tab").addClass("active");
					}
					if(logValue == 525600) {
						$("#nav-last-year-tab").addClass("active");
					}
				}
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

	$(document).ready( function makeDataTable() {
		$('#logging-table thead #logging-table-filter th').each( function () {
        	var title = $('#logging-table thead #logging-table-filter th').eq( $(this).index() ).text();
			if (/^(Details)$/.test(title)) {
				$(this).html('&nbsp;');
			} else {
				$(this).html('<input type="text" placeholder="Filter '+title+'" />');
			}
    	} );

		$("input[placeholder]").each(function () {
        	$(this).attr('size', $(this).attr('placeholder').length);
    	});
		
		$("#logging-table").DataTable({
			"columnDefs": [
				{
            		target: 4,
            		orderable: false
        		},
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
      					$("input", $("#logging-table thead #logging-table-filter th")[i]).val(col_search_val);
    				}
  				}
			}
		});

		var table = $("#logging-table").DataTable();

		// Get State
		if (table.state.loaded() != null) {
			tableState = table.state();
			
			// Enable all columns
			table.column(1).visible(true);
		}

		$("#logging-table thead #logging-table-filter input").on( 'keyup change', function () {
        table
            .column( $(this).parent().parent().index()+':visible' )
            .search( this.value )
            .draw();
    	} );

		// Hide columns after keyup change event registered
		if (table.state.loaded() == null) {
			table.column(1).visible(false);
		} else {
			if (!tableState.columns[1].visible) {
				table.column(1).visible(false)
			}
		}
	} );
</script>