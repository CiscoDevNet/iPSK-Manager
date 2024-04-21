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
	$sponsorChartLabel = "";
	$sponsorChartData = "";
	$captiveChartLabel = "";
	$captiveChartData = "";
	$adminChartLabel = "";
	$adminChartData = "";
	$addedEndpointsChartLabel = "";
	$addedEndpointsChartData = "";

	//$totalEndpointGroupCount = $ipskISEDB->getEndpointGroupCount();
	$totalEndpointCount = $ipskISEDB->getTotalEndpointCount();
	$expiredEndpointCount = $ipskISEDB->getTotalExpiredEndpointCount();
	$unknownEndpointCount = $ipskISEDB->getTotalUnknownEndpointCount();
	$endpointStats = $ipskISEDB->getDashboardStatsbyEPGroup();
	$sponsorLogins = $ipskISEDB->getSponsorLoginsLastSevenDays();
	$captiveLogins = $ipskISEDB->getCaptiveLoginsLastSevenDays();
	$adminLogins = $ipskISEDB->getAdminLoginsLastSevenDays();
	$addedEndpoints = $ipskISEDB->getAddedEndpointsLastSevenDays();

	if($endpointStats){
		while($row = $endpointStats->fetch_assoc()){
			$chartLabel .= "'".$row['groupName']."',";
			$chartData .= $row['endpointCount'].",";
		}
	}

	if($sponsorLogins){
		for ($x = 1; $x <= 7; $x++) { 
			$set = false;

			while($row = $sponsorLogins->fetch_assoc()){
				if (strcmp(trim($row['date']),trim(date('Y-m-d', strtotime('-'.$x.' days')))) == 0) {
					$sponsorChartLabel .= "'".$row['date']."',";
					$sponsorChartData .= $row['count'].",";
					$set = true;
					break;
				}
			}
			
			if (!$set) {
				$sponsorChartLabel .= "'".date('Y-m-d', strtotime('-'.$x.' days'))."',";
				$sponsorChartData .= "0,";
			}

			mysqli_data_seek($sponsorLogins,0);
		}
	}

	if($captiveLogins){
		for ($x = 1; $x <= 7; $x++) { 
			$set = false;

			while($row = $captiveLogins->fetch_assoc()){
				if (strcmp(trim($row['date']),trim(date('Y-m-d', strtotime('-'.$x.' days')))) == 0) {
					$captiveChartLabel .= "'".$row['date']."',";
					$captiveChartData .= $row['count'].",";
					$set = true;
					break;
				}
			}
			
			if (!$set) {
				$captiveChartLabel .= "'".date('Y-m-d', strtotime('-'.$x.' days'))."',";
				$captiveChartData .= "0,";
			}

			mysqli_data_seek($captiveLogins,0);
		}
	}

	if($adminLogins){
		for ($x = 1; $x <= 7; $x++) { 
			$set = false;

			while($row = $adminLogins->fetch_assoc()){
				if (strcmp(trim($row['date']),trim(date('Y-m-d', strtotime('-'.$x.' days')))) == 0) {
					$adminChartLabel .= "'".$row['date']."',";
					$adminChartData .= $row['count'].",";
					$set = true;
					break;
				}
			}
			
			if (!$set) {
				$adminChartLabel .= "'".date('Y-m-d', strtotime('-'.$x.' days'))."',";
				$adminChartData .= "0,";
			}

			mysqli_data_seek($adminLogins,0);
		}
	}

	if($addedEndpoints){
		for ($x = 1; $x <= 7; $x++) { 
			$set = false;

			while($row = $addedEndpoints->fetch_assoc()){
				if (strcmp(trim($row['date']),trim(date('Y-m-d', strtotime('-'.$x.' days')))) == 0) {
					$addedEndpointsChartLabel .= "'".$row['date']."',";
					$addedEndpointsChartData .= $row['count'].",";
					$set = true;
					break;
				}
			}
			
			if (!$set) {
				$addedEndpointsChartLabel .= "'".date('Y-m-d', strtotime('-'.$x.' days'))."',";
				$addedEndpointsChartData .= "0,";
			}

			mysqli_data_seek($addedEndpoints,0);
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

<div class="container-fluid">
    <div class="row">
      <!-- Chart 1 -->
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header">Sponsor Portal Authentications (Last 7 Days)</div>
          <div class="card-body">
            <canvas id="chart1"></canvas>
          </div>
        </div>
      </div>
      <!-- Chart 2 -->
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header">Captive Portal Authentications (Last 7 Days)</div>
          <div class="card-body">
            <canvas id="chart2"></canvas>
          </div>
        </div>
      </div>
      <!-- Chart 3 -->
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header">Admin Portal Authentications (Last 7 Days)</div>
          <div class="card-body">
            <canvas id="chart3"></canvas>
          </div>
        </div>
      </div>
      <!-- Chart 4 -->
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header">Total Endpoints By Group</div>
          <div class="card-body">
            <canvas id="chart4"></canvas>
          </div>
        </div>
      </div>
      <!-- Chart 5 -->
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header">Endpoint Information</div>
          <div class="card-body">
            <canvas id="chart5"></canvas>
          </div>
        </div>
      </div>
      <!-- Chart 6 -->
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-header">Endpoints Added (Last 7 Days)</div>
          <div class="card-body">
            <canvas id="chart6"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
		var sponsorConfig = {
			type: 'line',
			data: {
				datasets: [{
					data: [<?php print $sponsorChartData;?>],
					label: 'Authentications'
				}],
				labels: [<?php print $sponsorChartLabel;?>]
			},
			options: {
				responsive: true,
				plugins: {
            		legend: {
						position: 'bottom'
					}
				}
			}
		};

		var ctx1 = document.getElementById('chart1').getContext('2d');
    	var chart1 = new Chart(ctx1,sponsorConfig);

		var captiveConfig = {
			type: 'line',
			data: {
				datasets: [{
					data: [<?php print $captiveChartData;?>],
					label: 'Authentications'
				}],
				labels: [<?php print $captiveChartLabel;?>]
			},
			options: {
				responsive: true,
				plugins: {
            		legend: {
						position: 'bottom'
					}
				}
			}
		};

		var ctx2 = document.getElementById('chart2').getContext('2d');
    	var chart2 = new Chart(ctx2,captiveConfig);

		var adminConfig = {
			type: 'line',
			data: {
				datasets: [{
					data: [<?php print $adminChartData;?>],
					label: 'Authentications'
				}],
				labels: [<?php print $adminChartLabel;?>]
			},
			options: {
				responsive: true,
				plugins: {
            		legend: {
						position: 'bottom'
					}
				}
			}
		};

		var ctx3 = document.getElementById('chart3').getContext('2d');
    	var chart3 = new Chart(ctx3,adminConfig);

		var config = {
			type: 'doughnut',
			data: {
				datasets: [{
					data: [<?php print $chartData;?>],
					label: 'Endpoints'
				}],
				labels: [<?php print $chartLabel;?>]
			},
			options: {
				responsive: true,
				plugins: {
            		legend: {
						position: 'bottom'
					}
				}
			}
		};

		var ctx4 = document.getElementById('chart4').getContext('2d');
    	var chart4 = new Chart(ctx4,config);

		var endpointsConfig = {
			type: 'polarArea',
			data: {
				datasets: [{
					data: [<?php print $totalEndpointCount.','.$expiredEndpointCount.','.$unknownEndpointCount?>],
					label: 'Endpoints'
				}],
				labels: ['Total Endpoints','Expired Endpoints','Unknown Endpoints']
			},
			options: {
				responsive: true,
				plugins: {
            		legend: {
						position: 'bottom'
					}
				}
			}
		};

		var ctx5 = document.getElementById('chart5').getContext('2d');
    	var chart5 = new Chart(ctx5,endpointsConfig);

		var addedEndpointsConfig = {
			type: 'doughnut',
			data: {
				datasets: [{
					data: [<?php print $addedEndpointsChartData;?>],
					label: 'Endpoints'
				}],
				labels: [<?php print $addedEndpointsChartLabel;?>]
			},
			options: {
				responsive: true,
				plugins: {
            		legend: {
						position: 'bottom'
					}
				}
			}
		};

		var ctx6 = document.getElementById('chart6').getContext('2d');
    	var chart6 = new Chart(ctx6,addedEndpointsConfig);

		window.addEventListener('resize', function() {
      		chart1.resize();
      		chart2.resize();
      		chart3.resize();
			chart4.resize();
			chart5.resize();
			chart6.resize();
    	});
</script>