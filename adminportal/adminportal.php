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
	
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	//Check if Configuration file exists, if not redirect to Installer
	if(!file_exists("../supportfiles/include/config.php")){
		if(is_file("./installer.php") && is_file("./installer.inc.php")){
			require("./installer.php");
			exit(0);
		}else{
			header("Location: 404.php");
			die();
		}
	}
	
	//Core Components
	include("../supportfiles/include/config.php");
	include("../supportfiles/include/iPSKManagerFunctions.php");
	include("../supportfiles/include/iPSKManagerDatabase.php");
	
	ipskSessionHandler();

	$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);
	
	$ipskISEDB->set_encryptionKey($encryptionKey);
	
	//START-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	$encryptionKey = "";
	$dbPassword = "";
	unset($encryptionKey);
	unset($dbPassword);
	//END-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	
	if(!ipskLoginSessionCheck()){
		session_destroy();
		header("Location: ./");
		die();
	}
	
	//CHECK FOR INSTALLATION FILES
	$installerFilesPresent = false;
	
	if(is_file("./installer.php") || is_file("./installer.inc.php")){
		$installerFilesPresent = true;
	}else{
		$installerFilesPresent = false;
	}
	
	if($installerFilesPresent){
		$persistantAlert = <<< HTML
		<div style="z-index: 1090; height: 56px; width: 100%; pointer-events: none;" id="notifiationBar" class="fixed-top row">
			<div class="col"></div>
			<div style="pointer-events: auto;" id="notifiationWindow" class="shadow-lg text-center alert alert-danger col-4">
			<h6><strong>ALERT:  Installation Files are still installed.</strong></h6></div>
			<div class="col"></div>
		</div>
HTML;
	}else{
		$persistantAlert = "";
	}
	
	$adminMenuSetting = $ipskISEDB->getGlobalSetting("menu-config", "adminMenu");
	
	//Added Database Scheme Update Modal Dialog	for update to DB
	if($ipskISEDB->check_dbSchemaUpdates()){
		$newInstalationNotice = '';
		$schema = $ipskISEDB->getGlobalSetting("db-schema", "version");
		$requiredSchema = $ipskISEDB->get_requiredSchemaVersion();
		$databaseSchemeUpdate = <<< HTML
		<div class="modal fade" id="databaseUpdateDetected" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header shadow alert alert-danger">
						<h5 class="modal-title fw-bold" id="modalLongTitle">Database Update Required</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						  
						</button>
					</div>
					<div class="modal-body">
						<p class="h5" style="text-decoration: underline;">Database Schema Update Required:</p><br /><p class="h6">Your Schema Version: {$schema}<br />Required Schema Version: {$requiredSchema}<br /><br />Please review Database Change Log @ </p><p><a href="https://github.com/CiscoDevNet/iPSK-Manager/blob/master/DB_CHANGELOG.md">(GitHub) /CiscoDevNet/iPSK-Manager/blob/master/DB_CHANGELOG.md</a></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
HTML;
	}elseif ($ipskISEDB->check_newInstall()){
		
		$databaseSchemeUpdate = "";
		$newInstalationNotice = <<< HTML
		<div class="modal fade" id="newInstallDetected" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header shadow alert alert-warning">
						<h5 class="modal-title fw-bold" id="modalLongTitle">New Installation Detected</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						</button>
					</div>
					<div class="modal-body">
					<p class="h6">
    					Welcome to iPSK Manager!
    					<br /><br />
    					To ensure a smooth setup, it is important to follow a specific sequence during the initial configuration. Failing to do so may cause some menus to be unavailable.
    					<br /><br />
    					Begin by editing the relevant fields in the <span class="fw-bold">Platform Configuration</span> section. Next, configure any <span class="fw-bold">LDAP Servers</span> and define <span class="fw-bold">User Groups</span>
    					<br /><br />
    					After that, set up <span class="fw-bold">Wireless Networks</span> followed by <span class="fw-bold">Authorization Templates</span>, <span class="fw-bold">Endpoint Grouping</span> and <span class="fw-bold">Portal Groups</span> in that order.
    					<br /><br />
    					Once these configurations are complete, you can proceed to configure <span class="fw-bold">Portals</span> and finally add any <span class="fw-bold">Managed iPSK Endpoints</span>
						<br /><br />
					</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
HTML;
	}else {
		$databaseSchemeUpdate = "";
		$newInstalationNotice = "";
	}
?><!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<link rel="icon" href="../../../../favicon.png">

		<title>Admin Portal - iPSK Manager for Cisco ISE</title>

		<!-- Bootstrap core CSS -->
		<link href="styles/bootstrap.min.css" rel="stylesheet" >

		<!-- Custom styles for this template -->
		<link href="styles/dashboard.css" rel="stylesheet">
	</head>
	<body>
	<?php print $persistantAlert;?>
    <nav class="navbar navbar-dark fixed-top bg-primary flex-md-nowrap p-0 shadow">
		<div class="container-fluid">
			<a class="navbar-brand bg-primary col-sm-3 col-md-2 me-0" href="#">
				<img src="images/ipsk-logo.gif" width="180" height="32" />
				
			</a>
		</div>
	  <!-- <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search"> -->
	  <ul class="navbar-nav px-3">
		<li class="nav-item text-nowrap">
		  <a id="signOut" class="nav-link text-white" href="#">Sign out</a>
		</li>
	  </ul>
	</nav>
	<div class="container-fluid mt-5">
		<div class="row">
			<nav id="sidebarNav" class="col-md-2 d-none d-md-block bg-light sidebar p-2">
				<div class="sidebar-sticky">
					<ul class="nav flex-column">
<?php
			  
				if($adminMenuSetting){
					
					$adminMenu = json_decode($adminMenuSetting,TRUE);
					
					for($menuRow = 0; $menuRow < $adminMenu['menuItems']; $menuRow++) {
						
						print "<li class=\"nav-item\">\n";
							if($menuRow == 0){
								print "<a id=\"".$adminMenu[$menuRow]['id']."\" module=\"".$adminMenu[$menuRow]['module']."\" class=\"sideNav nav-link active\" href=\"#\">\n";
								print "<span data-feather=\"".$adminMenu[$menuRow]['data-feather']."\"></span>".$adminMenu[$menuRow]['menuText']."\n";
							}else{
								print "<a id=\"".$adminMenu[$menuRow]['id']."\" module=\"".$adminMenu[$menuRow]['module']."\" class=\"sideNav nav-link\" href=\"#\">\n";
								print "<span data-feather=\"".$adminMenu[$menuRow]['data-feather']."\"></span>".$adminMenu[$menuRow]['menuText']."\n";
							}
						print "</a>\n";
						print "</li>\n";
						
					}
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("adminPortalSettings"=>$adminPortalSettings));
					$logMessage = "REQUEST:FAILURE[error_loading_menu];ACTION:ADMINPORTAL-MENU;";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					print "Error Loading Menu";
				}
				
				if(file_exists("../supportfiles/adminportals/modules/additionalmenus.json")){
					
					$fileContents = file_get_contents("../supportfiles/adminportals/modules/additionalmenus.json");
					
					$adminMenu = json_decode($fileContents,TRUE);
					
					if($adminMenu){
						$itemCount = (isset($adminMenu['menuItems'])) ? $adminMenu['menuItems'] : 0;
						$menuEnabled = (isset($adminMenu['menuEnabled'])) ? $adminMenu['menuEnabled'] : false;
						
						if($itemCount > 0 && $menuEnabled){
							print '<hr class="m-0">';
							
							for($menuRow = 0; $menuRow < $adminMenu['menuItems']; $menuRow++) {
								print "<li class=\"nav-item\">\n";
								print "<a id=\"".$adminMenu[$menuRow]['id']."\" module=\"".$adminMenu[$menuRow]['module']."\" class=\"sideNav nav-link\" href=\"#\">\n";
								print "<span data-feather=\"".$adminMenu[$menuRow]['data-feather']."\"></span>".$adminMenu[$menuRow]['menuText']."\n";
								print "</a>\n";
								print "</li>\n";
							}
						}
					}
				}
?>
					</ul>
				</div>
			</nav>
			<main role="main" class="col-md-9 ms-sm-auto col-lg-10 p-2 px-4">
				<div id="mainContent" class="float-rounded mx-auto shadow-lg p-4 bg-white">
				
				</div>
				<div id="adminPortalNotifications">
					<?php print $newInstalationNotice;?>
					<?php print $databaseSchemeUpdate;?>
				</div>
			</main>
		</div>
	</div>
</body>
	<script type="text/javascript" src="scripts/jquery.min.js"></script>
	<!--<script type="text/javascript" src="scripts/popper.min.js"></script>-->
	<script type="text/javascript" src="scripts/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="scripts/feather.min.js"></script>
    <script type="text/javascript" src="scripts/chart.min.js"></script>
	<script type="text/javascript" src="scripts/clipboard.min.js"></script>
	<script type="text/javascript" src="scripts/ipsk-adminportal-v1.js"></script>
	<link href="styles/datatables.min.css" rel="stylesheet">
	<script type="text/javascript" src="scripts/datatables.min.js"></script>
	<script>
    $(document).ready(function () {
		// Clear Datatable Filters On Reload Or Page Change
    	$('.nav-link').click(function () {
			var table = $('#endpoint-table').DataTable();
			table.state.clear();
			var table = $('#logging-table').DataTable();
			table.state.clear();
    	});
		$(window).on('beforeunload', function() {
			var table = $('#endpoint-table').DataTable();
			table.state.clear();
			var table = $('#logging-table').DataTable();
			table.state.clear();
    	});
    });
  </script>
</html>
