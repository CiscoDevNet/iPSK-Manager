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
	
	//START-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	$encryptionKey = "";
	$dbPassword = "";
	unset($encryptionKey);
	unset($dbPassword);
	//END-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	
	$idxID = 0;
	
	$adminPortalSettings = $ipskISEDB->getGlobalClassSetting("admin-portal");
	$samlSettings = $ipskISEDB->getGlobalClassSetting("saml-settings");
	
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
									
					header("Location: 404.php");
				}
			}
		}else{
			if($adminPortalSettings['admin-portal-strict-hostname'] == true){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData();
				$logMessage = "REQUEST:FAILURE[admin_portal_hostname_no_match];ACTION:ADMINURLSTRICT;HOSTNAME:".$_SERVER['SERVER_NAME'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header("Location: 404.php");
			}
		}
	}

	$samlLogin = (isset($samlSettings['enabled'])) ? $samlSettings['enabled'] : false;
	
    if (!isset($_GET['error']) && $samlLogin == true) {
		header("Location: login.php");
		die();
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
		<div class="card mx-auto">
			<div class="card-header bg-primary mb-4">
  				<img src="images/ipsk-logo.gif" width="180" height="32" />
			</div>
			<div class="card-body">
				<h1 class="h4 mt-0 mb-4 fw-normal">iPSK Manager for Cisco ISE</h1>
				<h2 class="h5 mt-0 mb-4 fw-normal">Please Login</h2>
			<?php
				if(isset($_GET['error'])) {
			?>
					<div class="col">
						<div class="alert alert-danger shadow" role="alert">Authentication Failed</div>
					</div>
			<?php
				}
			?>
				<form action="login.php" method="post" class="form-signin">
					<label for="inputUsername" class="visually-hidden">Username</label>
					<input type="text" name="inputUsername" id="inputUsername" class="form-control mt-2 mb-3 shadow" placeholder="Username" required autofocus>
					<label for="inputPassword" class="visually-hidden">Password</label>
					<input type="password" name="inputPassword" id="inputPassword" class="form-control mt-2 mb-3 shadow" placeholder="Password" required>
			<?php 
				if($ipskISEDB->getLdapDirectoryCount() > 0){
					$ldapListing = $ipskISEDB->getLdapDirectoryListing();
					print "Please select an Authentication Source:";
					print '<select name="authDirectory" class="form-select mt-2 mb-3 shadow">';
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
					<button class="btn btn-primary btn-block mt-2 mb-3 shadow" id="loginbtn" type="submit">Sign in</button>
				</form>
			</div>
			<div class="card-footer">
				Copyright &copy; 2025 Cisco and/or its affiliates.
			</div>
		</div>
	</body>
</html>
<?php
}
?>