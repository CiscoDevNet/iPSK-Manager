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
	class BaseRESTCalls {
	
		private $restHost;
		private $restUsername;
		private $restPassword;
		private $restSSLVerifyPeer = true;
		private $iPSKManagerClass;
		
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
			$this->restSSLVerify = $sslVerifypeer;
		}
		
		function get_SSLVerifyPeer() {
			return $this->restSSLVerifyPeer;
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
				
				//Set the request URL in the CURL object
				curl_setopt($curlCall, CURLOPT_URL, $requestURL);
				
				//Set the SSL verification in the CURL object
				curl_setopt($curlCall, CURLOPT_SSL_VERIFYPEER, $this->restSSLVerifyPeer );
				
				//Set the Headers for the request in the CURL object
				curl_setopt($curlCall, CURLOPT_HTTPHEADER, $restCallHeader);
				
				//Check the restMethod (GET, POST, DELETE, PUT)
				if($restMethod == "GET"){
					
					//Method: GET
					curl_setopt($curlCall, CURLOPT_RETURNTRANSFER, true);
					
				}elseif($restMethod == "POST"){
					
					//Method: POST
					curl_setopt($curlCall, CURLOPT_POSTFIELDS, $data);
					curl_setopt($curlCall, CURLOPT_POST, 1);
					
				}elseif($restMethod == "DELETE"){
					
					//Method: DELETE
					curl_setopt($curlCall, CURLOPT_CUSTOMREQUEST, "DELETE");
					curl_setopt($curlCall, CURLOPT_RETURNTRANSFER, true);
				
				}elseif($restMethod == "PUT"){
					
					//Method: PUT
					curl_setopt($curlCall, CURLOPT_POSTFIELDS, $data);
					curl_setopt($curlCall, CURLOPT_CUSTOMREQUEST, "PUT");
					curl_setopt($curlCall, CURLOPT_RETURNTRANSFER, true);
				
				}else{
					//Method Not Found 				
					//Close the CURL Object
					curl_close ($curlCall);
					
					//Return False to the calling Method
					return false;
				}
				
				//Execute the Request and return the data response from the HTTP(s) server 
				$responseOutput = curl_exec($curlCall);
				
				//extract the CURL response parameters
				$responseArray = curl_getinfo($curlCall);
				
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
					$logMessage = "EXCEPTION:CAUGHT;EXCEPTION-ERROR:[".$e->getCode()."}];EXCEPTION-MESSAGE:".$e->getMessage().";HOSTNAME:".$_SERVER['SERVER_NAME'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";";
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