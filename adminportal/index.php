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

	//Check if Configuration file exists, if not redirect to Installer
	if(!file_exists("../supportfiles/include/config.php")){
		if(is_file("./installer.php") && is_file("./installer.inc.php")){
			require("./installer.php");
			exit(0);
		}else{
			header("Location: /404.php");
			die();
		}
	}
	
	//Core Components
	include("../supportfiles/include/config.php");
	include("../supportfiles/include/iPSKManagerFunctions.php");
	include("../supportfiles/include/iPSKManagerDatabase.php");
	
	ipskSessionHandler();

	$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);
	
	//START-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	$encryptionKey = "";
	$dbPassword = "";
	unset($encryptionKey);
	unset($dbPassword);
	//END-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	
	$idxID = 0;
	
	$adminPortalSettings = $ipskISEDB->getGlobalClassSetting("admin-portal");
	
	//LOG::Entry
	$logData = $ipskISEDB->generateLogData(Array("adminPortalSettings"=>$adminPortalSettings));
	$logMessage = "REQUEST:SUCCESS;ACTION:ADMINPORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";";
	$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
	
	if(strtolower($adminPortalSettings['admin-portal-hostname']) != strtolower($_SERVER['HTTP_HOST'])){	
		if($adminPortalSettings['redirect-on-hostname-match'] == true){	
			$portalDetails = $ipskISEDB->getPortalbyHostname($_SERVER['HTTP_HOST']);
			
			if($portalDetails){
				if($portalDetails['portalSecure'] ==  true){
					$callprotcol = "https";
				}else{
					$callprotcol = "http";
				}
				
				$redirectUrl = "$callprotcol://".$_SERVER['HTTP_HOST'].":".$portalDetails['portalTcpPort']."/index.php?portalId=".$portalDetails['portalId'];
				
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("redirectUrl"=>$redirectUrl));
				$logMessage = "REQUEST:SUCCESS;ACTION:HOSTNAMEREDIRECT;REDIRECT-URL:".$redirectUrl.";HOSTNAME:".$_SERVER['SERVER_NAME'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header("Location: ".$redirectUrl);
			}else{
				if($adminPortalSettings['admin-portal-strict-hostname'] == true && $adminPortalSettings['admin-portal-hostname'] != ""){
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData();
					$logMessage = "REQUEST:FAILURE[portal_hostname_lookup_failure];ACTION:HOSTNAMEREDIRECT;";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
									
					header("Location: /404.php");
				}
			}
		}else{
			if($adminPortalSettings['admin-portal-strict-hostname'] == true){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData();
				$logMessage = "REQUEST:FAILURE[admin_portal_hostname_no_match];ACTION:ADMINURLSTRICT;HOSTNAME:".$_SERVER['SERVER_NAME'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header("Location: /404.php");
			}
		}
	}
	
    if(isset($_GET['error'])){
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.png">

    <title>iPSK Manager for Cisco ISE</title>

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="styles/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <div class="float-rounded mx-auto shadow-lg p-2 bg-white">
		<form action="login.php" method="post" class="form-signin">
			<div class="mt-2 mb-4">
				<img src="images/iPSK-Logo.svg" width="108" height="57" />
			</div>
			<h1 class="h3 mt-2 mb-4 font-weight-normal">iPSK Manager for Cisco ISE</h1>
			<h2 class="h6 mt-2 mb-3 font-weight-normal">Please Login</h2>
			<div class="col">
				<div class="alert alert-danger shadow" role="alert">Authentication Failed</div>
			</div>
			<label for="inputEmail" class="sr-only">Username</label>
			<input type="text" name="inputUsername" id="inputUsername" class="form-control mt-2 mb-3 shadow" placeholder="Username" required autofocus>
			<label for="inputPassword" class="sr-only">Password</label>
			<input type="password" name="inputPassword" id="inputPassword" class="form-control mt-2 mb-3 shadow" placeholder="Password" required>
			<?php 
			
				if($ipskISEDB->getLdapDirectoryCount() > 0){
					$ldapListing = $ipskISEDB->getLdapDirectoryListing();
					print "Please select an Authentication Source:";
					print '<select name="authDirectory" class="form-control mt-2 mb-3 shadow">';
					print "<option value=\"0\">Internal</option>";
						
					while($row = $ldapListing->fetch_assoc()){
						if($idxID == 0){
							print "<option value=\"".$row['id']."\" selected>".$row['adConnectionName']."</option>";
							$idxID = 1;
						}else{
							print "<option value=\"".$row['id']."\">".$row['adConnectionName']."</option>";
						}
					}
					print "</select>";
				}else{
					print '<input type="hidden" name="authDirectory" value="0">';
				}
						
			?>
			<button class="btn btn-lg btn-primary btn-block mt-2 mb-3 shadow" id="loginbtn" type="submit">Sign in</button>
		</form>
		<p class="mt-5 mb-0 text-muted">Copyright &copy; 2019 Cisco and/or its affiliates.</p>
	</div>
  </body>
</html>


<?php
	}else{
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.png">

    <title>iPSK Manager for Cisco ISE</title>

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="styles/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <div class="float-rounded mx-auto shadow-lg p-2 bg-white">
		<form action="login.php" method="post" class="form-signin">
			<div class="mt-2 mb-4">
				<img src="images/iPSK-Logo.svg" width="108" height="57" />
			</div>
			<h1 class="h3 mt-2 mb-4 font-weight-normal">iPSK Manager for Cisco ISE</h1>
			<h2 class="h6 mt-2 mb-3 font-weight-normal">Please Login</h2>
			<label for="inputUsername" class="sr-only">Username</label>
			<input type="text" name="inputUsername" id="inputUsername" class="form-control mt-2 mb-3 shadow" placeholder="Username" required autofocus>
			<label for="inputPassword" class="sr-only">Password</label>
			<input type="password" name="inputPassword" id="inputPassword" class="form-control mt-2 mb-3 shadow" placeholder="Password" required>
			<?php 
				if($ipskISEDB->getLdapDirectoryCount() > 0){
					$ldapListing = $ipskISEDB->getLdapDirectoryListing();
					print "Please select an Authentication Source:";
					print '<select name="authDirectory" class="form-control mt-2 mb-3 shadow">';
					print "<option value=\"0\">Internal</option>";
						
					while($row = $ldapListing->fetch_assoc()){
						if($idxID == 0){
							print "<option value=\"".$row['id']."\" selected>".$row['adConnectionName']."</option>";
							$idxID = 1;
						}else{
							print "<option value=\"".$row['id']."\">".$row['adConnectionName']."</option>";
						}
					}
					print "</select>";
				}else{
					print '<input type="hidden" name="authDirectory" value="0">';
				}
			?>
			<button class="btn btn-lg btn-primary btn-block mt-2 mb-3 shadow" id="loginbtn" type="submit">Sign in</button>
		</form>
		<p class="mt-5 mb-0 text-muted">Copyright &copy; 2019 Cisco and/or its affiliates.</p>
	</div>
  </body>
  <script type="text/javascript" src="scripts/jquery-3.3.1.min.js"></script>
    <script type="text/javascript">

	</script>
</html>
<?php
}
?>