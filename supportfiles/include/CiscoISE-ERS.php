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
	
		function getEndPointIdentityGroups($pageSize = null, $page = null){
			
			if($pageSize != null || $page != null){
			
				$uriPath = "/ers/config/endpointgroup?size=$pageSize&page=$page";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return $apiSession['body'];
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
			}else{
				$uriPath = "/ers/config/endpointgroup?size=50";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if(isset($apiSessionResult['SearchResult']['nextPage']['href'])){
					$multiplePages = true;
				}else{
					$multiplePages = false;
				}
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					if($multiplePages == true){

						$currentResourceCount = 0;
						$iseEndpointGroupOutput['SearchResult']['total'] = $apiSessionResult['SearchResult']['total'];
						
						while($multiplePages){
							if(isset($apiSessionResult['SearchResult']['nextPage'])){
								$nextHref = substr($apiSessionResult['SearchResult']['nextPage']['href'],strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8), strlen($apiSessionResult['SearchResult']['nextPage']['href']) - strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8));
							}else{
								$nextHref = '';
							}
							
							foreach($apiSessionResult['SearchResult']['resources'] as $iseResource){
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['id'] = $iseResource['id'];
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['name'] = $iseResource['name'];
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['description'] = $iseResource['description'];
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['link'] = $iseResource['link'];
							
								$currentResourceCount++;
							}
								
							if($nextHref == ''){
								$multiplePages = false;
							}else{
								$headerArray = $this->ersRestContentTypeHeader;
								$apiSession = $this->restCall($nextHref, "GET", $headerArray, true);
								$apiSessionResult = json_decode($apiSession["body"], true);
								
								if($apiSession["http_code"] != 200){
									if($this->iPSKManagerClass){
										//LOG::Entry
										$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
										$logMessage = "API-REQUEST:FAILURE[incorrect_next_page_href];";
										$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
									}
									
									$multiplePages = false;
									
									return false;
								}
							}
						}
						
						if($this->iPSKManagerClass){
							//LOG::Entry
							$logjson = json_encode($iseEndpointGroupOutput);
							$logData = $this->iPSKManagerClass->generateLogData(Array("iseEndpointGroupOutput"=>$iseEndpointGroupOutput), Array("iseEndpointGroupOutput"=>$logjson));
							$logMessage = "API-REQUEST:SUCCESS[pageinated_summary];";
							$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						}
						
						return json_encode($iseEndpointGroupOutput);
					}else{
						return $apiSession['body'];
					}
					
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
			}
		}	

		function getEndPointGroupCountbyId($groupUuid){
						
			if($groupUuid != ''){
				$uriPath = "/ers/config/endpoint?filter=groupId.EQ.".$groupUuid;
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
				
				if($apiSession["http_code"] == 200){
					$tempApiSessionArray = json_decode($apiSession["body"],true);
					
					return $tempApiSessionArray['SearchResult']['total'];
				}else{
					return 0;
				}
			}else{
				return 0;
			}
		}

		function getEndPointsByEPGroup($groupUuid, $pageSize = null, $page = null){
			
			if($pageSize != null || $page != null){
				
				$uriPath = "/ers/config/endpoint?filter=groupId.EQ.".$groupUuid."&size=$pageSize&page=$page";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return $apiSession['body'];
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
			}else{
				$uriPath = "/ers/config/endpoint?size=50&filter=groupId.EQ.".$groupUuid."";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if(isset($apiSessionResult['SearchResult']['nextPage']['href'])){
					$multiplePages = true;
				}else{
					$multiplePages = false;
				}
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoints];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					if($multiplePages == true){

						$currentResourceCount = 0;
						$iseEndpointOutput['SearchResult']['total'] = $apiSessionResult['SearchResult']['total'];
						
						while($multiplePages){
							if(isset($apiSessionResult['SearchResult']['nextPage'])){
								$nextHref = substr($apiSessionResult['SearchResult']['nextPage']['href'],strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8), strlen($apiSessionResult['SearchResult']['nextPage']['href']) - strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8));
							}else{
								$nextHref = '';
							}
							
							foreach($apiSessionResult['SearchResult']['resources'] as $iseResource){
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['id'] = $iseResource['id'];
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['name'] = $iseResource['name'];
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['description'] = (isset($iseResource['description'])) ? $iseResource['description'] : '';
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['link'] = $iseResource['link'];
							
								$currentResourceCount++;
							}
								
							if($nextHref == ''){
								$multiplePages = false;
							}else{
								$headerArray = $this->ersRestContentTypeHeader;
								$apiSession = $this->restCall($nextHref, "GET", $headerArray, true);
								$apiSessionResult = json_decode($apiSession["body"], true);
								
								if($apiSession["http_code"] != 200){
									if($this->iPSKManagerClass){
										//LOG::Entry
										$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
										$logMessage = "API-REQUEST:FAILURE[incorrect_next_page_href];";
										$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
									}
									
									$multiplePages = false;
									
									return false;
								}
							}
						}
						
						if($this->iPSKManagerClass){
							//LOG::Entry
							$logjson = json_encode($iseEndpointOutput);
							$logData = $this->iPSKManagerClass->generateLogData(Array("iseEndpointOutput"=>$iseEndpointOutput), Array("iseEndpointOutputArray"=>$logjson));
							$logMessage = "API-REQUEST:SUCCESS[pageinated_summary];";
							$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						}
						
						return json_encode($iseEndpointOutput);
					}else{
						return $apiSession['body'];
					}
					
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
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
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[ise_authz_profile_other_error];";
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
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[ise_authz_profile_not_found];";
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