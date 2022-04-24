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