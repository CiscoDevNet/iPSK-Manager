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
	
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
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
		header("Location: /");
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
		<div style="z-index: 1090; height: 56px; width: 100%; pointer-events: none;" id="notifiationBar" class="fixed-top position-sticky row">
			<div class="col"></div>
			<div style="pointer-events: auto;" id="notifiationWindow" class="position-sticky shadow-lg text-center alert alert-danger col-4">
			<h6><strong>ALERT:  Installation Files are still Installed</strong></h6></div>
			<div class="col"></div>
		</div>
HTML;
	}else{
		$persistantAlert = "";
	}
	
	$adminMenuSetting = $ipskISEDB->getGlobalSetting("menu-config", "adminMenu");

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
    <nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
		<a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">
			<img src="images/iPSK-Logo.svg" width="61" height="32" />
			<span class="pl-2">iPSK Management Portal</span>
		</a>
	  <!-- <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search"> -->
	  <ul class="navbar-nav px-3">
		<li class="nav-item text-nowrap">
		  <a id="signOut" class="nav-link" href="#">Sign out</a>
		</li>
	  </ul>
	</nav>
	<div class="container-fluid">
		
		<div class="row">
			<nav id="sidebarNav" class="col-md-2 d-none d-md-block bg-light sidebar">
				<div class="sidebar-sticky">
					<ul class="nav flex-column">
<?php
			  
				if($adminMenuSetting){
					
					$adminMenu = json_decode($adminMenuSetting,TRUE);
					
					for($menuRow = 0; $menuRow < $adminMenu['menuItems']; $menuRow++) {
						
						print "<li class=\"nav-item\">\n";
							if($menuRow == 0){
								print "<a id=\"".$adminMenu[$menuRow]['id']."\" module=\"".$adminMenu[$menuRow]['module']."\" class=\"sideNav nav-link active\" href=\"#\">\n";
								print "<span data-feather=\"".$adminMenu[$menuRow]['data-feather']."\"></span>".$adminMenu[$menuRow]['menuText']."<span id=\"currentMenuItem\" class=\"sr-only\">(current)</span>\n";
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
?>
					</ul>
				</div>
			</nav>

			<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
				<div id="mainContent" class="float-rounded mx-auto shadow-lg p-2 bg-white">
				
				</div>
			</main>
		</div>
	</div>

</body>
	<script type="text/javascript" src="scripts/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="scripts/popper.min.js"></script>
	<script type="text/javascript" src="scripts/bootstrap.min.js"></script>
    <script type="text/javascript" src="scripts/feather.min.js"></script>
    <script type="text/javascript" src="scripts/Chart.bundle.min.js"></script>
	<script type="text/javascript" src="scripts/clipboard.min.js"></script>
	<script type="text/javascript" src="scripts/ipsk-adminportal-v1.js"></script>
</html>
