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

	
	
	
/**
 *@author	Gary Oppel (gaoppel@cisco.com)
 *@author	Hosuk Won (howon@cisco.com)
 *@contributor	Drew Betz (anbetz@cisco.com)
 */
	
	class CiscoISEMnTRestAPI extends BaseRESTCalls {
	
		function getISEMntHostname(){
			//Returns the Following 
			//	hostname of the ISE appliance
			//	Extracts the hostname from the URL that was registered
			//	when initalizing the class
			//	Parses out the hostname from the FQDN of the URL registered
			
			$mntHostname = parent::get_restHost();
			
			$mntArray = explode(".",parse_url($mntHostname,PHP_URL_HOST));

			return $mntArray[0];

		}
		
		function getEndpointSessionbyID($sessionID){
			//Returns the Following 
			//		XML Object of the Session
			//    /admin/API/mnt/Session/Active/SessionID/c0a805a0000006245be325b5/0
			//Check for valid user first
			
			//Build the REST API Query
			$uriPath = "/admin/API/mnt/Session/Active/SessionID/".$sessionID."/0";
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}
		
		function getEndpointSessionbyIP($endpointIP){
			//Returns the Following 
			//		XML Object of the Session
			//    /admin/API/mnt/Session/Active/SessionID/c0a805a0000006245be325b5/0
			//Check for valid user first
			
			//Build the REST API Query
			$uriPath = "/ise/mnt/api/Session/EndPointIPAddress/".$endpointIP;
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}

		function getEndpointSessionbyMAC($macAddress){
			//Returns the Following 
			//		XML Object of the Session
			//    /admin/API/mnt/Session/MACAddress/00:00:00:00:00:00
			//Check for valid user first
			
			//Build the REST API Query
			$uriPath = "/admin/API/mnt/Session/MACAddress/".$macAddress;
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}

		function invokeSessionCoAReauthDefault($iseMntServer, $macAddress){
			//Performs a Session Change of Authorization with Default
			$uriPath = "/admin/API/mnt/CoA/Reauth/".$iseMntServer."/".$macAddress."/0";
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				$returnedxml = simplexml_load_string($apiSession["body"]);
				$json = json_encode($returnedxml);
				$result = json_decode($json,TRUE);
				
				if($result["results"] == "true"){
					return true;
				}else{
					return false;
				}
			}else{
				return "invalid";
			}
		}
			
		function invokeSessionCoAReauthLast($iseMntServer, $macAddress){
			//Performs a Session Change of Authorization with Last 
			$uriPath = "/admin/API/mnt/CoA/Reauth/".$iseMntServer."/".$macAddress."/1";
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				$returnedxml = simplexml_load_string($apiSession["body"]);
				$json = json_encode($returnedxml);
				$result = json_decode($json,TRUE);
				
				if($result["results"] == "true"){
					return true;
				}else{
					return false;
				}
			}else{
				return "invalid";
			}
		}
		
		function invokeSessionCoAReauthRerun($iseMntServer, $macAddress){
			//Performs a Session Change of Authorization with Reauthentication 
			$uriPath = "/admin/API/mnt/CoA/Reauth/".$iseMntServer."/".$macAddress."/2";
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				$returnedxml = simplexml_load_string($apiSession["body"]);
				$json = json_encode($returnedxml);
				$result = json_decode($json,TRUE);
				
				if($result["results"] == "true"){
					return true;
				}else{
					return false;
				}
			}else{
				return "invalid";
			}
		}
		
		function invokeSessionCoADisconnectDefault($iseMntServer, $macAddress){
			//Performs a Session Change of Authorization with Default
			$uriPath = "/admin/API/mnt/CoA/Disconnect/".$iseMntServer."/".$macAddress."/0";
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				$returnedxml = simplexml_load_string($apiSession["body"]);
				$json = json_encode($returnedxml);
				$result = json_decode($json,TRUE);
				
				if($result["results"] == "true"){
					return true;
				}else{
					return false;
				}
			}else{
				return "invalid";
			}
		}
		
		function invokeSessionCoADisconnectBounce($iseMntServer, $macAddress){
			//Performs a Session Change of Authorization with Default
			$uriPath = "/admin/API/mnt/CoA/Disconnect/".$iseMntServer."/".$macAddress."/1";
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				$returnedxml = simplexml_load_string($apiSession["body"]);
				$json = json_encode($returnedxml);
				$result = json_decode($json,TRUE);
				
				if($result["results"] == "true"){
					return true;
				}else{
					return false;
				}
			}else{
				return "invalid";
			}
		}
		
		function invokeSessionCoADisconnectShutdown($iseMntServer, $macAddress){
			//Performs a Session Change of Authorization with Default
			$uriPath = "/admin/API/mnt/CoA/Disconnect/".$iseMntServer."/".$macAddress."/2";
			
			$headerArray = array('');
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				$returnedxml = simplexml_load_string($apiSession["body"]);
				$json = json_encode($returnedxml);
				$result = json_decode($json,TRUE);
				
				if($result["results"] == "true"){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
	}
	
	
?>