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
	$maxModuleKeywordLength = 15;
		
	$subModuleRegEx = "/^(?:create|new|add|edit|view|delete|enable|disable|extend|modify|update|suspend|activate|groups|updategroups|pass|updatepass|authzprofile)$/";
	
	function ipskSessionHandler(){
		
		session_start();

		if(!isset($_SESSION['creationTime'])) {
			$_SESSION['creationTime'] = time();
			$_SESSION['authenticationGranted'] = false;
			$_SESSION['authorizationGranted'] = false;
			$_SESSION['authenticationTimestamp'] = false;
			$_SESSION['authorizationTimestamp'] = false;
			
			$octets = explode(".",$_SERVER['REMOTE_ADDR']);
			$_SESSION['sessionID'] = sprintf("%02x%02x%02x%02x-%08x", $octets[0], $octets[1], $octets[2], $octets[3], time());
		}
	}

	function ipskLoginSessionCheck(){
		
		if(isset($_SESSION)){
			if(isset($_SESSION['loggedIn']) && isset($_SESSION['logonDN']) && isset($_SESSION['logonUsername']) && isset($_SESSION['authenticationGranted']) && isset($_SESSION['authorizationGranted'])){
				if($_SESSION['loggedIn'] == true && $_SESSION['authenticationGranted'] == true && $_SESSION['authorizationGranted'] == true){
					if(isset($_SESSION['logoutTimer'])){
						if($_SESSION['authorizationGranted'] > time()){
							return true;
						}else{
							return false;
						}
					}else{
						return true;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}	
	}
	
	function convertBinSID($binSid) {
		//Break apart the byte stream
		$stringSid = unpack('Crevision/x/nauthhigh/Nauthlow/V*', $binSid);
		
		//Rebuild in String Form
		$secuirtyId = sprintf("S-%u-%d-%u-%u-%u-%u-%u", $stringSid['revision'], ($stringSid['authhigh'] << 32) + $stringSid['authlow'], $stringSid[1], $stringSid[2], $stringSid[3], $stringSid[4], $stringSid[5]);

		return $secuirtyId;
	}

	function generateGuid(){
		//Generate new Guid with cryptographically secure pseudo-random integers
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 65535), random_int(0, 65535), random_int(0, 65535), random_int(16384, 20479), random_int(32768, 49151), random_int(0, 65535), random_int(0, 65535), random_int(0, 65535));
	}

	function sanitizeGetModuleInput($RegEx){
		
		
		$arguments = array(
			'module'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-Z]|[a-z]|[0-9]){1,50}$/')
								),
			'sub-module'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
									  'flags'     => '', 
									  'options'   => array('regexp' => $RegEx)
								),
			'module-action'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-Z]|[a-z]){1,15}$/')
								),
			'id'	=>	FILTER_VALIDATE_INT,
			'logoff'	=>	FILTER_VALIDATE_BOOLEAN,
			'action'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-Z]|[a-z]|[0-9]|_){1,25}$/')
								),
			'groupUuid'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-F]|[a-f]|[0-9]){8}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){12}$/')
								),
			'uploadkey'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-F]|[a-f]|[0-9]){8}$/')
								),
			'confirmaction'	=>	FILTER_VALIDATE_INT,
			'wirelessSSID'	=>	FILTER_VALIDATE_INT,
			'ssidName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ssidDescription'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzPolicyName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzPolicyDescription'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'termLengthSeconds'	=>	FILTER_VALIDATE_INT,
			'ciscoAVPairPSK'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'pskLength'	=>	FILTER_VALIDATE_INT,
			'pskType'	=>	FILTER_VALIDATE_INT,
			'pskMode'	=>	FILTER_VALIDATE_INT,
			'epGroupName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'epGroupDescription'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzTemplate'	=>	FILTER_VALIDATE_INT,
			'notificationPermission'	=>	FILTER_VALIDATE_INT,
			'maxDevices'	=>	FILTER_VALIDATE_INT,
			'sponsorGroupName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'sponsorGroupDescription'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'sponsorGroupAuthType'	=>	FILTER_VALIDATE_INT,
			'sponsorGroupType'	=>	FILTER_VALIDATE_INT,
			'sponsorPortalType'	=>	FILTER_VALIDATE_INT,
			'sponsorGroupName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'suspendCheck'	=>	FILTER_VALIDATE_INT,
			'unsuspendCheck'	=>	FILTER_VALIDATE_INT,
			'extendCheck'	=>	FILTER_VALIDATE_INT,
			'deleteCheck'	=>	FILTER_VALIDATE_INT,
			'editCheck'	=>	FILTER_VALIDATE_INT,
			'createCheck'	=>	FILTER_VALIDATE_INT,
			'viewPassCheck'	=>	FILTER_VALIDATE_INT,
			'viewPermission'	=>	FILTER_VALIDATE_INT,
			'resetPskCheck'	=>	FILTER_VALIDATE_INT,
			'groupName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'groupDn'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'portalName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzProfileName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'description'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'hostname'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'tcpPort'	=>	FILTER_VALIDATE_INT,
			'permission'	=>	FILTER_VALIDATE_INT,
			'groupType'	=>	FILTER_VALIDATE_INT,
			'authDirectory'	=>	FILTER_VALIDATE_INT,
			'inputUsername'	=> FILTER_SANITIZE_EMAIL,
			'userName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'fullName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'email'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'password'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'confirmpassword'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adConnectionName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adServer'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adDomain'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adUsername'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adBaseDN'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adSecure'	=>	FILTER_VALIDATE_INT,
			'associationGroup'	=>	FILTER_VALIDATE_INT,
			'editAssociation'	=>	FILTER_VALIDATE_INT,
			'editPSK'	=>	FILTER_VALIDATE_INT,
			'presharedKey'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'emailAddress'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'macAddress'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'endpointDescription'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'fullName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'hostname'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'protocol'	=>	FILTER_VALIDATE_INT,
			'portalPort'	=>	FILTER_VALIDATE_INT,
			'ersHost'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ersUsername'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ersPassword'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ersEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'ersVerifySsl'	=>	FILTER_VALIDATE_BOOLEAN,
			'mntHost'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntHostSecondary'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntUsername'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntPassword'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'mntVerifySsl'	=>	FILTER_VALIDATE_BOOLEAN,
			'fullAuthZUpdate'	=>	FILTER_VALIDATE_BOOLEAN,
			'adminPortalHostname'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'strict-hostname'	=>	FILTER_VALIDATE_BOOLEAN,
			'redirect-hostname'	=>	FILTER_VALIDATE_BOOLEAN,
			'smtpHost'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpPort'	=>	FILTER_VALIDATE_INT,
			'smtpUsername'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpFromAddress'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'portalPskEditEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'advancedLoggingSettings'	=>	FILTER_VALIDATE_BOOLEAN,
			'portalPskEditCheck'	=>	FILTER_VALIDATE_INT,
			'bulkCreateCheck'	=>	FILTER_VALIDATE_INT,
			'bulkImportType'	=>	FILTER_VALIDATE_INT,
			'sqlLogging'	=>	FILTER_VALIDATE_INT,
			'payloadLogging'	=>	FILTER_VALIDATE_INT,
			'debugLogging'	=>	FILTER_VALIDATE_INT,
			'getLogging'	=>	FILTER_VALIDATE_INT,
			'postLogging'	=>	FILTER_VALIDATE_INT,
			'sessionLogging'	=>	FILTER_VALIDATE_INT,
			'serverLogging'	=>	FILTER_VALIDATE_INT,
			'complexLowercase'	=>	FILTER_VALIDATE_INT,
			'complexUppercase'	=>	FILTER_VALIDATE_INT,
			'complexNumbers'	=>	FILTER_VALIDATE_INT,
			'complexSpecial'	=>	FILTER_VALIDATE_INT,
			'complexSimilar'	=>	FILTER_VALIDATE_INT
		);
		
		$mysanitizedInputs = filter_input_array(INPUT_POST, $arguments);

		return $mysanitizedInputs;
	}

	function sanitizeGetDataInput($dataCommandRegEx, $dataDataSetRegEx, $dataInputName, $dataInputFilter){
			
		$arguments = array(
			'data-command'	=>	array('filter' => FILTER_VALIDATE_REGEXP, 'flags' => '' , 'options' => array('regexp' => $dataCommandRegEx)),
			'data-set'	=>	array('filter' => FILTER_VALIDATE_REGEXP, 'flags' => '' , 'options' => array('regexp' => $dataDataSetRegEx)),
			'pskLength'	=>	FILTER_VALIDATE_INT,
			'authzProfileName'	=>	array('filter'	=>	FILTER_SANITIZE_STRING,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH & FILTER_FLAG_STRIP_BACKTICK
								  ),
			"$dataInputName" =>	$dataInputFilter
		);
		
		$mysanitizedInputs = filter_input_array(INPUT_POST, $arguments);

		return $mysanitizedInputs;
		
	}

	/* START: Functions for sorting the returned EP Groups
	 * array from ISE ERS.
	 */

	/* Support function for sorting ISE Return Results.
	 * This was created to sort the Endpoint Identity Groups array
	 */
	function ____alphaSorting($left, $right){
		$result = strcmp($left["name"], $right["name"]);
		return $result;
	}

	/* Function that checks the input and validates it meets the criteria.
	 * This returns the array back; however, the 'name' field is sorted
	 */
	function arraySortAlpha($inputArray){
		if(isset($inputArray['SearchResult']['total'])){
			if($inputArray['SearchResult']['total'] > 7){
				if(isset($inputArray['SearchResult']['resources'][0]['name'])){
					$mySortedResources = $inputArray['SearchResult']['resources'];
					usort($mySortedResources,'____alphaSorting');
					$inputArray['SearchResult']['resources'] = $mySortedResources;

					return $inputArray;

				}else{
					return $inputArray;
				}
			}else{
				return $inputArray;
			}
		}else{
			return $inputArray;
		}
	}
	/* END: Functions for sorting the returned EP Groups
	 * array from ISE ERS.
	 */
?>