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