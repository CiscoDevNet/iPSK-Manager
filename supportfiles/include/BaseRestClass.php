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
	class BaseRESTCalls {
	
		private $restHost;
		private $restUsername;
		private $restPassword;
		private $restSSLVerifyPeer = true;
        private $connectTimeoutMs = 5000;
        private $transferTimeoutMs = 30000;
		protected $iPSKManagerClass;
		
		function __construct($hostname = null, $username = null, $password = null, $sslVerifypeer = true, $ipskManagerClass = false) {		
			$this->restHost = $hostname;
			$this->restUsername = $username;
			$this->restPassword = $password;
			$this->restSSLVerifyPeer = $sslVerifypeer;
			$this->iPSKManagerClass = $ipskManagerClass;
		}
		
		function set_restHost($hostname) {
			$this->restHost = $hostname;
		}
		
		function get_restHost(){
			return $this->restHost;
		}
		
		function set_Username($username) {
			$this->restUsername = $username;
		}
		
		function get_Username(){
			return $this->restUsername;
		}
		
		function set_Password($password) {
			$this->restPassword = $password;
		}
		
		private function get_Password(){
			return $this->ersPassword;
		}
		
		function set_SSLVerifyPeer($sslVerifypeer) {
			$this->restSSLVerifyPeer = (bool)$sslVerifypeer;
		}
		
		function get_SSLVerifyPeer() {
			return $this->restSSLVerifyPeer;
		}
		
        function set_timeouts($connectMs, $transferMs) {
            if (!is_int($connectMs) || !is_int($transferMs) || $connectMs < 1 || $transferMs < $connectMs || $transferMs > 300000) {
                throw new InvalidArgumentException('Timeouts must be positive milliseconds, connect <= transfer <= 300000');
            }
            $this->connectTimeoutMs = $connectMs;
            $this->transferTimeoutMs = $transferMs;
        }

		function restCall($restURLPath, $restMethod, $restCallHeader, $basicAuth = false, $data = null){
			//Provides basic restAPI Call to a web based API interface
			//This method is multi-use and provides flexibility to make calls with various inputs
			//
			//Input:
			//		$requestURL		= URL path to append to the restHost within this Class
			//		$restMethod 	= HTTP Method to invoke with the rest call (GET, POST, DELETE, PUT)
			//		$restCallHeader	= Headers to append to the HTTP(s) request (An Array is Expected to be Passed)
			//		$basicAuth		= Boolean used to include basic authentication with the parameters set during this classes initialization
			//		$data			= Data to be sent with the HTTP(s) request
			//Output:
			//		$responseArray	= Array of the request which includes ["body"], ["http_code"], and many more variables for the communciation
			
			//Generate Full URL Path
			$requestURL = $this->restHost.$restURLPath;
			
			//Check if Basic Authentication is required and append Headers if required
			if($basicAuth == true){
				//Create Basic Authorization header to send to HTTP(s) server
				$authenticationHeader = 'Authorization: Basic ' . base64_encode($this->restUsername.":".$this->restPassword);
				
				//Push Authorization into the Array stack
				array_push($restCallHeader, $authenticationHeader);
			}
			
			//
			try {
			
				//Create CURL object to prepare the HTTP(s) call
				$curlCall = curl_init();
                curl_setopt($curlCall, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlCall, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeoutMs);
                curl_setopt($curlCall, CURLOPT_TIMEOUT_MS, $this->transferTimeoutMs);
				
				//Set the request URL in the CURL object
				curl_setopt($curlCall, CURLOPT_URL, $requestURL);
				
				//Set the SSL peer verification in the CURL object
				curl_setopt($curlCall, CURLOPT_SSL_VERIFYPEER, $this->restSSLVerifyPeer );
				
				// Certificate chain and hostname checks do not require optional OCSP stapling.

				//Set the SSL host verification in the CURL object
				curl_setopt($curlCall, CURLOPT_SSL_VERIFYHOST, $this->restSSLVerifyPeer ? 2 : 0 );
				
				//Set the Headers for the request in the CURL object
				curl_setopt($curlCall, CURLOPT_HTTPHEADER, $restCallHeader);
				
				//Check the restMethod (GET, POST, DELETE, PUT)
				if($restMethod == "GET"){
					
					//Method: GET
					
				}elseif($restMethod == "POST"){
					
					//Method: POST
					curl_setopt($curlCall, CURLOPT_POSTFIELDS, $data);
					curl_setopt($curlCall, CURLOPT_POST, 1);
					
				}elseif($restMethod == "DELETE"){
					
					//Method: DELETE
					curl_setopt($curlCall, CURLOPT_CUSTOMREQUEST, "DELETE");
				
				}elseif($restMethod == "PUT"){
					
					//Method: PUT
					curl_setopt($curlCall, CURLOPT_POSTFIELDS, $data);
					curl_setopt($curlCall, CURLOPT_CUSTOMREQUEST, "PUT");
				
				}else{
					//Method Not Found 				
					//Close the CURL Object
					curl_close ($curlCall);
					
					//Return False to the calling Method
					return false;
				}
				
				//Execute the Request and return the data response from the HTTP(s) server 
				$responseOutput = curl_exec($curlCall);
				
				if($responseOutput === false){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$curlerror = curl_error($curlCall);
						$logData = $this->iPSKManagerClass->generateLogData(Array("curl_error"=>$curlerror));
						$logMessage = "EXCEPTION:CAUGHT;CURL-EXCEPTION:;CURL-ERROR-MESSAGE:".$curlerror.";";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
				}
				
				//extract the CURL response parameters
				$responseArray = curl_getinfo($curlCall);
                $responseArray['error_code'] = curl_errno($curlCall);
                $responseArray['error'] = curl_error($curlCall);
				
				//Close the CURL Object
				curl_close ($curlCall);
				
			} catch (Exception $e) {
				//Copy Trace Variables to new Variable
				$exceptionData = $e->getTrace();
				
				//START-[DO NOT REMOVE] - EMPTIES/REMOVES ARGUMENTS ARRAY FROM THE EXCEPTION TRACE VARIABLE
				foreach($exceptionData as $keyname => $value){
					//Copy Exception Array into new Array
					$traceOutput[$keyname] = $value;
					//Strip Function Arguments to Protect User Input
					unset($traceOutput[$keyname]['args']);
				}
				//END-[DO NOT REMOVE] - EMPTIES/REMOVES ARGUMENTS ARRAY FROM THE EXCEPTION TRACE VARIABLE
				
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("exceptionData"=>$traceOutput), Array("restURLPath"=>$restURLPath), Array("restMethod"=>$restMethod), Array("restCallHeader"=>$restCallHeader), Array("basicAuth"=>$basicAuth));
					$logMessage = "EXCEPTION:CAUGHT;CURL-EXCEPTION:;EXCEPTION-ERROR:[".$e->getCode()."}];EXCEPTION-MESSAGE:".$e->getMessage().";HOSTNAME:".$_SERVER['SERVER_NAME'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			//Append the Reponse Data to the ["body"] key with in the Array
			$responseArray["body"] = $responseOutput;
			
			//Return Response
			return $responseArray;
		}
	}
?>