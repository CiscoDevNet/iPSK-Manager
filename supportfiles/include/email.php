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

	function sendEmail($to, $subject, $body, $smtpSettings){			
		
		$sendTo = filter_var($to, FILTER_SANITIZE_EMAIL);		
		
		if($sendTo != ""){
			
			if($smtpSettings['smtp-fromaddress'] != ''){
				$mailFrom = filter_var($smtpSettings['smtp-fromaddress'], FILTER_SANITIZE_EMAIL);
			}else{
				$mailFrom = "ipskmanager@system.local";
			}
			
			$headers = 'To: ' . $to . "\r\n";
			
			$headers .= 'From: ' . $mailFrom . "\r\n";
			$headers .=	'Reply-To: ' . $mailFrom . "\r\n";
			$headers .=	'X-Mailer: PHP/' . phpversion();
			//$headers .= "MIME-Version: 1.0\r\n";
			//$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			if(mail($to, $subject, $body, $headers, "-f ".$mailFrom)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	function sendHTMLEmail($to, $portalName, $wirelessPassword, $wirelessSsid, $smtpSettings){
		$sendTo = filter_var($to, FILTER_SANITIZE_EMAIL);		
		
		if($sendTo != ""){
			
			if($smtpSettings['smtp-fromaddress'] != ''){
				$mailFrom = filter_var($smtpSettings['smtp-fromaddress'], FILTER_SANITIZE_EMAIL);
			}else{
				$mailFrom = "ipskmanager@system.local";
			}
			
			$headers = 'To: ' . $to . "\r\n";
			
			$headers .= 'From: ' . $mailFrom . "\r\n";
			$headers .=	'Reply-To: ' . $mailFrom . "\r\n";
			$headers .=	'X-Mailer: PHP/' . phpversion();
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			$subject = $portalName." Enrollment";
			$body = <<< HTML
<html>
<head>
</head>
<body>
<h1>Successfully Enrolled</h1>
<table>
<thead>
<tr>
<th>Pre-Shared Key</th>
<th>SSID Name</th>
</tr>
</thead>
</tbody>
<tr>
<td>$wirelessPassword</td>
<td>$wirelessSsid</td>
</tr>
</tbody>
</table>
</body>			
</html>			
HTML;
			
			
			if(mail($to, $subject, $body, $headers, "-f ".$mailFrom)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		
	}

?>