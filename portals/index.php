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
 

	//Check if Configuration file exists, if not redirect to 404 Not Found
	if(!file_exists("../supportfiles/include/config.php")){
		header("Location: /404.php");
		die();
	}
	
	//Core Components
	include("../supportfiles/include/config.php");
	include("../supportfiles/include/iPSKManagerFunctions.php");
	include("../supportfiles/include/iPSKManagerDatabase.php");
	include("../supportfiles/include/BaseRestClass.php");
	include("../supportfiles/include/CiscoISE-MnT.php");
	include("../supportfiles/include/CiscoISE-ERS.php");
	
	//Optional Components per Page
	include("../supportfiles/include/BaseLDAPClass.php");
	include("../supportfiles/include/email.php");
	
	ipskSessionHandler();
	
	$iseMNTIntegrationAvailable = false;
	$iseERSIntegrationAvailable = false;
	
	$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);

	$ipskISEDB->set_encryptionKey($encryptionKey);
	
	//START-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	$encryptionKey = "";
	$dbPassword = "";
	unset($encryptionKey);
	unset($dbPassword);
	//END-[DO NOT REMOVE] - EMPTIES/REMOVES ENCRYTION KEY/DB PASSWORD VARIABLE
	
	$sanitizedInput = sanitizeGetModuleInput($subModuleRegEx);
		
	$ersCreds = $ipskISEDB->getISEERSSettings();

	$samlSettings = $ipskISEDB->getGlobalClassSetting("saml-settings");
	
	if($ersCreds['enabled']){
				
		if(!isset($ersCreds['verify-ssl-peer'])){
			$ersCreds['verify-ssl-peer'] = true;
		}
		
		$ipskISEERS = new CiscoISEERSRestAPI($ersCreds['ersHost'], $ersCreds['ersUsername'], $ersCreds['ersPassword'], $ersCreds['verify-ssl-peer'], $ipskISEDB);
		$iseERSIntegrationAvailable = true;
	}
	
	$ersCreds = "";
	
	$mntCreds = $ipskISEDB->getISEMnTSettings();
	
	if($mntCreds['enabled']){
		
		if(!isset($mntCreds['verify-ssl-peer'])){
			$mntCreds['verify-ssl-peer'] = true;
		}
		
		$ipskISEMNT = new CiscoISEMnTRestAPI($mntCreds['mntHost'], $mntCreds['mntUsername'], $mntCreds['mntPassword'], $mntCreds['verify-ssl-peer'], $ipskISEDB);
		$iseMNTIntegrationAvailable = true;
	}
	
	$mntCreds = "";
	
	$getPortalId = isset($_GET['portalId']) ?  $_GET['portalId'] : '';
	$getPortal = isset($_GET['portal']) ?  $_GET['portal'] : '';
	$getError = isset($_GET['error']) ?  $_GET['error'] : '';
	
	if(isset($_GET['client_mac'])){
		$_SESSION['portalGET']['client_mac'] = $_GET['client_mac'];
	}
		
	if(isset($_GET['redirect'])){
		$_SESSION['portalGET']['redirect'] = $_GET['redirect'];
	}	
		
	if(isset($_GET['sessionId'])){
		$_SESSION['portalGET']['sessionId'] = $_GET['sessionId'];
	}	

	if($getPortal == "" && $samlSettings['enabled'] == true && $getError == ""){
		$getPortal = "login.php";
	} elseif ($getPortal == "") {
		$getPortal = "index.php";
	}
	// fix/test when getportal something other then blank or index.php
	//echo $getPortal;
	//die();
	$sessionData = $_SESSION;
	
	if($getPortalId != "" && $getPortal != ""){
		$portalId = $_GET['portalId'];
		$portalSettings = $ipskISEDB->getPortalByGuid($portalId);
		
		//Check Portal HTTPS Enforcement
		if($portalSettings['enforceSecure'] && $portalSettings['portalSecure']){
			if(!$_SERVER['HTTPS']){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[none_https_enforced];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header ('Location: /404.php');
				die();
			}
		}
		
		//Check Portal TCP Port Enforcement
		if($portalSettings['enforceTcpPort']){
			if($_SERVER['SERVER_PORT'] != $portalSettings['portalTcpPort']){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[tcp_port_mismatch_enforce];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";TCPPORT:".$_SERVER['SERVER_PORT'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header ('Location: /404.php');
				die();
			}
		}

		//Check Hostname Portal Enforcement
		if($portalSettings['enforceHostname']){
			if(strtolower($_SERVER['SERVER_NAME']) != strtolower($portalSettings['portalHostname'])){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[hostname_mismatch_enforce];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";TCPPORT:".$_SERVER['SERVER_PORT'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header ('Location: /404.php');
				die();
			}
		}
		
		if($portalSettings){
			$_SESSION['portalSettings'] = $portalSettings;
			
			$filename = "../supportfiles/portals/".$portalSettings['portalModule']."/".$portalSettings['portalTemplate']."/".substr($getPortal,0,strlen($getPortal) - 4).".inc.php";
			if(file_exists($filename)){
				header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
				header("Cache-Control: post-check=0, pre-check=0", false);
				header("Pragma: no-cache");
				
				include ($filename);
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("filename"=>$filename), Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:FAILURE[portal_file_not_found];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";TCPPORT:".$_SERVER['SERVER_PORT'].";REQUESTED-FILE:".$filename.";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				header ('Location: /404.php');
				die();
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("filename"=>$filename), Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:FAILURE[portal_instance_not_found];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";GUID:".$portalId.";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
			header ('Location: /404.php');
			die();
		}
	}elseif(isset($_GET['portal'])){
		if(isset($_SESSION['portalSettings'])){
			$filename = "../supportfiles/portals/".$_SESSION['portalSettings']['portalModule']."/commonfiles/".$_GET['portal'];
			if(file_exists($filename)){
				switch(substr($filename,strlen($filename) - 3, 3)) {
					case "css":
						$contentType = "text/css";
						break;
					case ".js":
						$contentType = "text/javascript";
						break;
					case "svg":
						$contentType = "image/svg+xml";
						break;
					case "gif":
						$contentType = "image/gif";
						break;
					case "png":
						$contentType = "image/png";
						break;
					case "jpg":
						$contentType = "image/jpeg";
						break;
					case "ico":
						$contentType = "image/x-icon";
						break;
					case "jpeg":
						$contentType = "image/jpeg";
						break;
					case "txt":
						$contentType = "text/plain";
						break;
					default:
						//LOG::Entry
						$logData = $ipskISEDB->generateLogData(Array("filename"=>$filename), Array("sanitizedInput"=>$sanitizedInput));
						$logMessage = "FILEREQUEST:FAILURE[file_found_no_match];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";PORTAL-GET:".$_GET['portal'].";FILE-NAME:$filename;";
						$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						
						header('HTTP/1.1 403 Forbidden');
						die('Forbidden');
				}
				
				header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
				header("Cache-Control: post-check=0, pre-check=0", false);
				header("Pragma: no-cache");
				
				header ("Content-Type: $contentType");

				$fileContents = file_get_contents($filename);
				print $fileContents;
				die();
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("filename"=>$filename), Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "FILEREQUEST:FAILURE[file_not_found];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";PORTAL-GET:".$_GET['portal'].";FILE-NAME:$filename;";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
				header ("Location: /404.php");
				die();
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
			$logMessage = "REQUEST:FAILURE[portal_instance_not_found];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";PORTAL-GET:".$_GET['portal'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			header ("Location: /404.php");
		}
	}else{
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:FAILURE[unknown_request];ACTION:PORTAL;HOSTNAME:".$_SERVER['SERVER_NAME'].";PORTAL-GET:".$_GET['portal'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
		header ("Location: /404.php");
	}
?>