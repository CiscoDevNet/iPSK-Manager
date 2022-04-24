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