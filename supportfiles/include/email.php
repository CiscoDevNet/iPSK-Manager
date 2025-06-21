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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("../supportfiles/include/phpmailer/Exception.php");
require("../supportfiles/include/phpmailer/PHPMailer.php");
require("../supportfiles/include/phpmailer/SMTP.php");

	function sendSMTPEmail($to, $portalName, $wirelessPassword, $wirelessSsid, $macAddress, $endpointGroupName, $description, $fullname, $enrolledBy, $smtpSettings, $ipskISEDB){

		$mail = new PHPMailer(true);

		try {

			//Server settings
			//$mail->SMTPDebug  = 3;
			//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                     
			$mail->isSMTP();        
			$mail->Host = $smtpSettings['smtp-hostname'];
			if(($smtpSettings['smtp-username'] !='') && ($smtpSettings['smtp-password'] !='')) {
				$mail->SMTPAuth = true;
				$mail->Username = $smtpSettings['smtp-username'];
				$mail->Password = $smtpSettings['smtp-password'];
			}
			else {
				$mail->SMTPAuth = false;
			}
			if($smtpSettings['smtp-encryption'] == 'STARTTLS') {
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			}
			if($smtpSettings['smtp-encryption'] == 'TLS') {
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			}
			$mail->Port = $smtpSettings['smtp-port'];

			//Recipients
			if($smtpSettings['smtp-fromaddress'] != ''){
				$mailFrom = filter_var($smtpSettings['smtp-fromaddress'], FILTER_SANITIZE_EMAIL);
			}else{
				$mailFrom = "ipskmanager@system.local";
			}
			$mail->setFrom($mailFrom, 'iPSK Manager');
			$mail->addAddress($to); 
			$mail->addReplyTo($mailFrom, 'iPSK Manager');

			//Content
			$mail->isHTML(true);  //Set email format to HTML
			$mail->Subject = $portalName." Enrollment";
			$mail->Body    = htmlBody($portalName, $wirelessPassword, $wirelessSsid, $macAddress, $endpointGroupName, $description, $fullname, $enrolledBy);
			$mail->AltBody = "You have successfully been enrolled in $portalName access. Please use the following passcode: $wirelessPassword when connecting to the $wirelessSsid wireless network\n\nThank you!";
			
			$mail->send();
			$logMessage = "EMAIL:SUCCESS;ACTION:SEND;METHOD:SMTP;SERVER:".$smtpSettings['smtp-hostname'].";TO:".$to.";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__);
		} catch (Exception $e) {
			$logMessage = "EMAIL:FAILURE;ACTION:SEND;METHOD:SMTP;ERROR:".$mail->ErrorInfo.":SERVER:".$smtpSettings['smtp-hostname'].";TO:".$to.";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__);
		}
	}

	function htmlBody($portalName, $wirelessPassword, $wirelessSsid, $macAddress, $endpointGroupName, $description, $fullname, $enrolledBy){
		$body = <<< HTML
<html>
	<head>
		<style>
		table {
		  width: 100%;
		}
		</style>
	</head>
	<body>
		<div style="width: 20%; float: left;">&nbsp;</div>
		<div style="width: 60%; float: left;">
			<div style="background-color: #1ba0d7;"><h1 style="text-align:center; color: #ffffff;">Successfully Enrolled</h1></div>
			<p>You have successfully been enrolled in $portalName access.</p> 
			
			<p><span style="font-weight: bold;">Registered Name:</span>$fullname</p>
			<p><span style="font-weight: bold;">Description:</span>$description</p>
			<p><span style="font-weight: bold;">Device MAC Address:</span>$macAddress</p>
			<p><span style="font-weight: bold;">Endpoint Group Name:</span>$endpointGroupName</p>
			
			<p>Below you will find the applicable settings to connect to the WiFi network:</p>
			<table>
				<thead>
					<tr style="background-color: #c0c0c0;">
						<th><p style="font-size: larger;">SSID Name</p></th>
						<th><p style="font-size: larger;">Pre-Shared Key</p></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="text-align: center;">$wirelessSsid</td>
						<td style="text-align: center;">$wirelessPassword</td>
					</tr>
				</tbody>
			</table>
			<div style="background-color: #1ba0d7;"><h3><p style="color: #ffffff; text-align: center;">Copyright &copy; 2025 Cisco and/or its affiliates.</p></h3></div>
		</div>
		<div style="width: 20%; float: right;">&nbsp;</div>
	</body>	
</html>
HTML;
		return $body;
	}

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
			
			if(mail($to, $subject, $body, $headers, "-f ".$mailFrom)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	function sendHTMLEmail($to, $portalName, $wirelessPassword, $wirelessSsid, $macAddress, $endpointGroupName, $description, $fullname, $enrolledBy, $smtpSettings, $ipskISEDB){
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
			$body = htmlBody($portalName, $wirelessPassword, $wirelessSsid, $macAddress, $endpointGroupName, $description, $fullname, $enrolledBy);

			if(mail($to, $subject, $body, $headers, "-f ".$mailFrom)){
				$logMessage = "EMAIL:SUCCESS;ACTION:SEND;METHOD:LOCALSERVER;TO:".$to.";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__);
				return true;
			}else{
				$logMessage = "EMAIL:FAILURE;ACTION:SEND;METHOD:LOCALSERVER;TO:".$to.";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__);
				return false;
			}
		}else{
			return false;
		}
		
	}

?>