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
	
	$chartLabel = ""; 
	$chartData = "";
	$chartColor = "";
	
	$totalEndpointGroupCount = $ipskISEDB->getEndpointGroupCount();
	$totalEndpointCount = $ipskISEDB->getTotalEndpointCount();
	$expiredEndpointCount = $ipskISEDB->getTotalExpiredEndpointCount();
	$unknownEndpointCount = $ipskISEDB->getTotalUnknownEndpointCount();
	
	$endpointStats = $ipskISEDB->getDashboardStatsbyEPGroup();
	
	if($endpointStats){
		while($row = $endpointStats->fetch_assoc()){
			$chartLabel .= "'".$row['groupName']."',";
			$chartData .= $row['endpointCount'].",";
			$chartColor .= "'rgba(".random_int(0, 255).", ".random_int(0, 255).", ".random_int(0, 255).", 0.4)',";
		}
	}
?>
<div class="row">
	<div class="col-12"><h1 class="text-center">iPSK Dashboard</h1></div>
</div>
<div class="row">
	<div class="col-12"><h6 class="text-center">Welcome to the iPSK Management Portal for Cisco ISE</h6></div>
</div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div class="row text-center">
	<div class="col">
		<div class="row">
			<div class="col"><h3>Total Endpoint Groups</h3></div>
		</div>
		<div class="row">
			<div class="col"><h2 class="text-danger"><?php print $totalEndpointGroupCount;?></h2></div>
		</div>
	</div>
	<div class="col">
		<div class="row">
			<div class="col"><h3>Total Endpoints</h3></div>
		</div>
		<div class="row">
			<div class="col"><h2 class="text-danger"><?php print $totalEndpointCount;?></h2></div>
		</div>
	</div>
	<div class="col">
		<div class="row">
			<div class="col"><h3>Expired Endpoints</h3></div>
		</div>
		<div class="row">
			<div class="col"><h2 class="text-danger"><?php print $expiredEndpointCount;?></h2></div>
		</div>
	</div>
	<div class="col">
		<div class="row">
			<div class="col"><h3>Unknown Endpoints</h3></div>
		</div>
		<div class="row">
			<div class="col"><h2 class="text-danger"><?php print $unknownEndpointCount;?></h2></div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col"><hr></div>
</div>
<div class="row">
	<div class="col">
		<div id="canvas-holder" style="width:40%">
				<canvas id="chart-area-eps"></canvas>
		</div>
	</div>
</div>
<div id="canvas-holder" style="width:40%">
		<canvas id="chart-area-epgroups"></canvas>
</div>
<div id="canvas-holder" style="width:40%">
		<canvas id="chart-area-associations"></canvas>
</div>
<script>
		var config = {
			type: 'pie',
			data: {
				datasets: [{
					data: [<?php print $chartData;?>],
					backgroundColor: [<?php print $chartColor;?>],
					label: 'Endpoints by Group'
				}],
				labels: [<?php print $chartLabel;?>]
			},
			options: {
				responsive: true
			}
		};

		var ctx = document.getElementById('chart-area-eps').getContext('2d');
		window.myPie = new Chart(ctx, config);
</script>