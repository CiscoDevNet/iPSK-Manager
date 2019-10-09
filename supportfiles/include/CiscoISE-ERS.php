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
	
	class CiscoISEERSRestAPI extends BaseRESTCalls {
		
		private $ersRestContentType = "json";
		private $ersRestContentTypeHeader = array('Accept: application/json', 'Content-Type: application/json');
		
		function set_ersContentType($contentType) {
			//Set the Content Type for all ERS methods
			if($contentType == "json"){
				$this->ersRestContentType = "json";
				$ersRestContentTypeHeader = array('Accept: application/json', 'Content-Type: application/json');
				return true;
			}elseif($contentType == "xml"){
				$this->ersRestContentType = "xml";
				$ersRestContentTypeHeader = array('Accept: application/xml', 'Content-Type: application/xml');
				return true;
			}else{
				return false;
			}				
		}
		
		function get_ersContentType(){
			return $this->ersRestContentType;
		}		
		
		function getEndPointbyMac($macAddress){
						
			$uriPath = "/ers/config/endpoint?filter=mac.EQ.".$macAddress;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}
		
		function getEndPointDetailsbyId($endpointId){
						
			$uriPath = "/ers/config/endpoint/".$endpointId;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}
		
		function check_ifAuthZProfileExists($name){
			
			$uriPath = "/ers/config/authorizationprofile/name/".$name;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:SUCCESS[ise_authz_profile_found];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return true;
			}elseif($apiSession["http_code"] == 404){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[ise_authz_profile_not_found];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			
		}
		
		function getAuthorizationProfile($name){
			
			$uriPath = "/ers/config/authorizationprofile/name/".$name;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:SUCCESS[ise_authz_profile_found];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return $apiSession["body"];
			}elseif($apiSession["http_code"] == 404){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[failure_to_create_ise_authz_profile];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			
		}
		
		function createAuthorizationProfile($data){
			
			$uriPath = "/ers/config/authorizationprofile";
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "POST", $headerArray, true, $data);
			
			if($apiSession["http_code"] == 201){
				return true;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[create_ise_authz_profile_failure];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			
		}
		
		function updateEndPointDetailsbyId($endpointId, $data){
						
			$uriPath = "/ers/config/endpoint/".$endpointId;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "PUT", $headerArray, true, $data);
			
			if($apiSession["http_code"] == 200){
				return true;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[failure_to_update_ise_endpoint_by_id];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
		}
		
		function createCaptivePortalAuthzProfile($profileName, $profileDescription, $portalUrl){
			
			if(!$this->check_ifAuthZProfileExists($profileName)){
				$authzProfile = '{"AuthorizationProfile":{"name":"","description":"","advancedAttributes":[{"leftHandSideDictionaryAttribue":{"AdvancedAttributeValueType":"AdvancedDictionaryAttribute","dictionaryName":"Cisco","attributeName":"cisco-av-pair"},"rightHandSideAttribueValue":{"AdvancedAttributeValueType":"AttributeValue","value":""}}],"accessType":"ACCESS_ACCEPT","authzProfileType":"SWITCH","trackMovement":false,"serviceTemplate":false,"easywiredSessionCandidate":false,"voiceDomainPermission":false,"neat":false,"webAuth":false,"profileName":"Cisco"}}';
				
				//Convert JSON to Array
				$authzProfileArray = json_decode($authzProfile,TRUE);
				
				//Setup URL the Settings
				$redirectUrl = "url-redirect=".$portalUrl."&sessionId=SessionIdValue&client_mac=ClientMacValue";
				
				//Setup the Required Settings
				$authzProfileArray['AuthorizationProfile']['name'] = $profileName;
				$authzProfileArray['AuthorizationProfile']['description'] = $profileDescription;
				$authzProfileArray['AuthorizationProfile']['advancedAttributes'][0]['rightHandSideAttribueValue']['value'] = $redirectUrl;
				
				$authzJsonData = json_encode($authzProfileArray);
				
				if($this->createAuthorizationProfile($authzJsonData)){
					return true;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("authzProfileArray"=>$authzProfileArray), Array("authzJsonData"=>$authzJsonData));
						$logMessage = "API-REQUEST:FAILURE[failure_to_create_ise_authz_profile];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}	
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("authzProfileArray"=>$authzProfileArray), Array("authzJsonData"=>$authzJsonData));
					$logMessage = "API-REQUEST:FAILURE[authz_profile_ise_already_exists];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			
		}
	}	
?>