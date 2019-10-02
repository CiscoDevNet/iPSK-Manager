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
	
	
	//Displaying of Errors Global setting
	error_reporting(E_ALL);
	ini_set('display_errors', 'Off');
	ini_set('display_startup_errors', 'Off');
	
	//SET GLOBAL VARIABLES
	$globalSmtpEnabled = false;
	$globalDateOutputFormat = 'm/d/Y @ g:i A';
	
	//ORGANIZATION SID VARIABLES
	$baseSid = "S-1-9";
	$orgSid = "-";
	$systemSid = "1";
	
	$encryptionKey = '';

	$dbHostname = '';
	$dbUsername = '';
	$dbPassword = '';
	$dbDatabase = '';

?>