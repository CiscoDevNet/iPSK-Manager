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
 *@contributor	Nick Ciesinski (nciesins@cisco.com)
*/
	
	ini_set('display_errors', 'Off');
	session_start();
	
	//Check if Configuration file exists, if it does return error 404 when trying to access installer
	//Ignore if we are currently running the installer on the system
	if(!isset($_SESSION['identityPSKInstalling'])){
		if(file_exists("../supportfiles/include/config.php")){
			http_response_code(404);
			header("Location: 404.php");
			exit(0);
		}
	}
	
	$license = <<< TEXT
	Copyright 2024 Cisco Systems, Inc. or its affiliates

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

		http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
TEXT;

	$installerOutput = "";
	$platformDetails = "";
	$platformValid = true;
	$dbCreateFlag = false;
	$managerUserCreateFlag = false;
	$iseUserCreateFlag = false;
	$upgrade = false;
	$inContainer = false;
	
	//Length of DB User Password
	$passwordLength = 20;
	
	//Installer Local Functions

	function generatePassword($length = 8, $complexity = 15){
		//Define the Alphabet Array for Selectable Complexity
		$alphabet = '';
		$alphabetArray = Array(1=>"abcdefghijkmnopqrstuvwxyz", 2=>"ABCDEFGHJKLMNPQRSTUVWXYZ", 4=>"123456789", 8=>'!?#$%@*',16=>'lIO0');
				
		//Generate Alphabet String based on user defined Complexity
		foreach($alphabetArray as $index => $entry){
			if($complexity & $index){
				$alphabet .= $entry;
			}
		}
		
		//Limit the maximum length to 64 Characters
		if($length > 64){ $length = 64;}
		
		$generatedPsk = "";
		//Loop through and select random characters from the alphabet
		for($char = 0; $char < $length; $char++){
			$generatedPsk .= substr($alphabet, random_int(0,strlen($alphabet)) - 1, 1);
		}

		return $generatedPsk;
	}
	
	function generateEncryptionKey(){
		$key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
		return base64_encode($key);
	}

	if(is_file("/opt/ipsk-manager/config.php")) {
		$upgrade = true;
	}
	
	if(is_file("/.dockerenv")) {
		$inContainer = true;
	}

	//Main Installer Code
	if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['step'])){
		if($_POST['step'] == 1){
			$installerOutput = <<< HTML
			<form method="POST" action="./installer.php">
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col"><textarea style="height: 350px; width: 750px;" readonly>$license</textarea></div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col"><strong>Click Next to continue Setup.</strong></div>
					<div class="col-2"></div>
				</div>
				<div class="row">
					<div class="col"></div>
					<div class="col-2"><input type="hidden" name="step" value="2"><input type="submit" id="btnNext" value="Next >" class="btn btn-primary shadow"></div>
				</div>
			</form>
HTML;
		}elseif($_POST['step'] == 2){
			
			//Check PHP Version & Extensions for Proper Operation
			if (version_compare(PHP_VERSION, '7.2.0') >= 0){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Version <strong>'".PHP_VERSION."'</strong></div>"; $platformValid = true; }else{ $platformDetails .=  "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Version '".PHP_VERSION."' - <strong>Version > 7.2 Required</strong></div>"; $platformValid = false;}
			if (extension_loaded('mbstring')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'mbstring'</strong> Installed</div>"; }else{ $platformDetails .=  "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'mbstring'</strong> is NOT Installed</div>"; $platformValid = false;}
			if (extension_loaded('ldap')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'ldap'</strong> Installed</div>"; }else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'ldap'</strong> is NOT Installed</div>"; $platformValid = false;}
			if (extension_loaded('mysqli')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'mysqli'</strong> Installed</div>"; }else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'mysqli'</strong> is NOT Installed</div>"; $platformValid = false;}
			if (extension_loaded('mysqlnd')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'mysqlng'</strong> Installed</div>"; }else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'mysqlng'</strong> is NOT Installed</div>"; $platformValid = false;}
			if (extension_loaded('curl')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'curl'</strong> Installed</div>"; }else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'curl'</strong> is NOT Installed</div>"; $platformValid = false;}  
			if (extension_loaded('simplexml')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'simplexml'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'simplexml'</strong> is NOT Installed</div>"; $platformValid = false;}
			if (extension_loaded('xml')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'xml'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'xml'</strong> is NOT Installed</div>"; $platformValid = false;}
			if (extension_loaded('sodium')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'sodium'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'sodium'</strong> is NOT Installed</div>"; $platformValid = false;}
			if (extension_loaded('json')){ $platformDetails .=  "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span> PHP Extension <strong>'json'</strong> Installed</div>";}else{ $platformDetails .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span> PHP Extension <strong>'json'</strong> is NOT Installed</div>"; $platformValid = false;}
			
			if($platformValid == true){
				$nextText = "Click Next to continue to Database Setup.";
				$nextButton = '<input type="hidden" name="step" value="3"><input type="submit" id="btnNext" value="Next >" class="btn btn-primary shadow">';
			}else{
				$nextText = "System Requirement check failure, please confirm system prerequisites before proceeding.";
				$nextButton = '';
			}
				
			$installerOutput = <<< HTML
			<form method="POST" action="./installer.php">
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><h4>PHP Validation Checks</h4> $platformDetails</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col"><strong>$nextText</strong></div>
					<div class="col-2"></div>
				</div>
				<div class="row">
					<div class="col"></div>
					<div class="col-2">$nextButton</div>
				</div>
			</form>
			<script type="text/javascript">
				$(function() {	
					feather.replace()
				});
			</script>

HTML;
		}elseif($_POST['step'] == 3){
			$installerOutput = <<< HTML
			<form method="POST" action="./installer.php">
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><h4>MySQL Database Parameters</h4></div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start">
						<label class="fw-bold" for="dbhostname">MySQL Server IP/FQDN:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="text" my-field-state="required" class="form-control shadow my-form-field" id="dbhostname" name="dbhostname">
							<div class="invalid-feedback">Please enter a valid Username</div>
						</div>
						<label class="fw-bold" for="dbusername">iPSK Database Username:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="text" my-field-state="required" class="form-control shadow my-form-field" id="dbusername" name="dbusername">
							<div class="invalid-feedback">Please enter a Name</div>
						</div>
						<label class="fw-bold" for="iseusername">Cisco ISE ODBC Username:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="text" my-field-state="required" class="form-control shadow my-form-field" id="iseusername" name="iseusername">
							<div class="invalid-feedback">Please enter a Name</div>
							<div class="fw-bold small" id="usernamefeedback"></div>
						</div>
						<label class="fw-bold" for="databasename">iPSK Database Name:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="text" my-field-state="required" class="form-control shadow my-form-field" id="databasename" name="databasename">
							<div class="invalid-feedback">Please enter a Name</div>
						</div>
						<label class="fw-bold" for="rootusername">MySQL Admin/Root Username:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="text" class="form-control shadow" id="rootusername" name="rootusername">
						</div>
						<label class="fw-bold" for="rootpassword">MySQL Admin/Root Password:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="password" my-field-state="required" class="form-control shadow my-form-field" id="rootpassword" name="rootpassword">
							<div class="invalid-feedback">Please enter a password</div>
						</div>
					</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col"><strong>Click Next to continue Setup.</strong></div>
					<div class="col-2"></div>
				</div>
				<div class="row">
					<div class="col"></div>
					<div class="col-2"><input type="hidden" name="step" value="4"><input type="submit" id="btnNext" value="Next >" class="btn btn-primary shadow"></div>
				</div>
			</form>
			<script type="text/javascript">			
				$("#dbusername,#iseusername").keyup(function(event){
					var dbusername = $("#dbusername").val();
					var iseusername = $("#iseusername").val();

					if(dbusername != iseusername){
						$("#usernamefeedback").addClass('text-success');
						$("#usernamefeedback").removeClass('text-danger');
						$("#usernamefeedback").html('');
						$("#btnNext").prop("disabled", false);
					}else{
						$("#usernamefeedback").removeClass('text-success');
						$("#usernamefeedback").addClass('text-danger');
						$("#usernamefeedback").html('iPSK DB and ISE Username can not be the same!');
						$("#btnNext").prop("disabled", true);
					}
					pass = "";
					confirmpass = "";
					
				});
			</script>
HTML;
		}elseif($_POST['step'] == 4){
			$_SESSION['dbhostname'] = (isset($_POST['dbhostname'])) ? $_POST['dbhostname'] : '';
			$_SESSION['dbusername'] = (isset($_POST['dbusername'])) ? $_POST['dbusername'] : '';
			$_SESSION['iseusername'] = (isset($_POST['iseusername'])) ? $_POST['iseusername'] : '';
			$_SESSION['databasename'] = (isset($_POST['databasename'])) ? $_POST['databasename'] : '';
			$_SESSION['rootusername'] = (isset($_POST['rootusername'])) ? $_POST['rootusername'] : '';
			$_SESSION['rootpassword'] = (isset($_POST['rootpassword'])) ? $_POST['rootpassword'] : '';
			
			$installerOutput = <<< HTML
			<form method="POST" action="./installer.php">
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><h4>iPSK Manager's Administrator Password</h4></div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start">
						<label class="fw-bold" for="password">Administrator Password:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="password" class="form-control shadow" id="password" name="password" autocomplete="off">
							<div class="invalid-feedback">Please enter a password</div>
						</div>
						<label class="fw-bold" for="confirmpassword">Confirm Administrator Password:</label>
						<div class="mb-3 input-group-sm fw-bold">
							<input type="password" class="form-control shadow" id="confirmpassword" autocomplete="off">
							<div class="invalid-feedback">Please confirm your password</div>
							<div class="fw-bold small" id="passwordfeedback"></div>
						</div>
					</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col"><strong>Click Next to continue Setup.</strong></div>
					<div class="col-2"></div>
				</div>
				<div class="row">
					<div class="col"></div>
					<div class="col-2"><input type="hidden" name="step" value="5"><input type="submit" id="btnNext" value="Next >" class="btn btn-primary shadow" disabled></div>
				</div>
			</form>
			<script type="text/javascript">			
				$("#password,#confirmpassword").keyup(function(event){
					var pass = $("#password").val();
					var confirmpass = $("#confirmpassword").val();

					if(pass == confirmpass && confirmpass.length > 7){
						$("#passwordfeedback").addClass('text-success');
						$("#passwordfeedback").removeClass('text-danger');
						$("#passwordfeedback").html('Passwords Match!');
						$("#btnNext").prop("disabled", false);
					}else{
						$("#passwordfeedback").removeClass('text-success');
						$("#passwordfeedback").addClass('text-danger');
						$("#passwordfeedback").html('Passwords must match and be at least 8 characters long!');
						$("#btnNext").prop("disabled", true);
					}
					pass = "";
					confirmpass = "";
					
				});
			</script>
HTML;
		}elseif($_POST['step'] == 5){
			$_SESSION['adminpassword'] = (isset($_POST['password'])) ? $_POST['password'] : '';
			
			$installerOutput = <<< HTML
			<form method="POST" action="./installer.php">
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-center"><h5>Please confirm the following setup parameters:</h5></div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><strong>MySQL Server IP/FQDN:</strong> {$_SESSION['dbhostname']}</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><strong>iPSK Database Username:</strong> {$_SESSION['dbusername']}</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><strong>Cisco ISE ODBC Username:</strong> {$_SESSION['iseusername']}</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><strong>iPSK Database Name:</strong> {$_SESSION['databasename']}</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><strong>MySQL Admin/Root Username:</strong> {$_SESSION['rootusername']}</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col"><strong>Click Install to begin Installation.</strong></div>
					<div class="col-2"></div>
				</div>
				<div class="row">
					<div class="col"></div>
					<div class="col-2"><input type="hidden" name="step" value="6"><input type="submit" id="btnNext" value="Install" class="btn btn-primary shadow"></div>
				</div>
			</form>
HTML;
		}elseif($_POST['step'] == 6){
			$installProgress = "";
			$_SESSION['installSuccess'] = false;
			
			$orgTime = time();
			
			$baseSid = "S-1-9";
			$orgSid = "$orgTime-$orgTime";
			$systemSid = "1";
			
			$systemSID = "$baseSid-$orgSid-1";
			$adminSID = "$baseSid-$orgSid-500";
			
			$managerDbPassword = generatePassword($passwordLength);
			$iseDbPassword = generatePassword($passwordLength);
			
			$encryptionKey = generateEncryptionKey();
			
			$ipskManagerAdminPassword = password_hash($_SESSION['adminpassword'], PASSWORD_DEFAULT);
			
			try {
				$dbConnection = new mysqli($_SESSION['dbhostname'], $_SESSION['rootusername'], $_SESSION['rootpassword']);
			}
			catch (Exception $e) {
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>MySQL Error: ".$e."</div>";
				goto Bail;
			}
		
			$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>MySQL Connection Successful</div>";
							
			try {
				if($dbConnection->select_db($_SESSION['databasename'])){
					$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Database Already Exists</div>";
					goto Bail;
				}
			}
			catch (Exception $e){
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Database is not in use</div>";
			}
			
			$dbConnection->select_db("mysql");
			$createDbQuery = sprintf("CREATE DATABASE `%s`", $dbConnection->real_escape_string($_SESSION['databasename']));
			
			if(!$dbConnection->query($createDbQuery)){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Database Create Error: (".$dbConnection->connect_errno.") ".$dbConnection->connect_error."</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Database Created Successfully</div>";
				$dbCreateFlag = true;
			}
			
			$dbConnection->select_db($_SESSION['databasename']);
			$actionValid = true;
			
			include_once("installer.inc.php");
			
			for($sqlCount = 0; $sqlCount < count($sqlProcedure); $sqlCount++){
				if(!$dbConnection->query($sqlProcedure[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Stored Procedure Creation Failure</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Stored Procedures Created Successfully</div>";
			}
			
			for($sqlCount = 0; $sqlCount < count($sqlTable); $sqlCount++){
				if(!$dbConnection->query($sqlTable[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Table Creation Failure</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Tables Created Successfully</div>";
			}

			for($sqlCount = 0; $sqlCount < count($sqlTrigger); $sqlCount++){
				if(!$dbConnection->query($sqlTrigger[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Trigger Creation Failure</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Triggers Created Successfully</div>";
			}

			for($sqlCount = 0; $sqlCount < count($sqlAlterTable); $sqlCount++){
				if(!$dbConnection->query($sqlAlterTable[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Altering of Tables Failed</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Altering of Tables Successful</div>";
			}
			
			for($sqlCount = 0; $sqlCount < count($sqlConstraint); $sqlCount++){
				if(!$dbConnection->query($sqlConstraint[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Setting of Contraints Failed</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Setting of Contraints Successful</div>";
			}
			
			for($sqlCount = 0; $sqlCount < count($sqlInsert); $sqlCount++){
				if(!$dbConnection->query($sqlInsert[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Inserting of inital data Failed</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Inserting of initial data Successful</div>";
			}
			try {
				if(!$dbConnection->query($managerSqlUser[0])){
					$actionValid = false;
				}
			}
			catch (Exception $e) {
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Error creating iPSK Manager User</div>";
				goto Bail;
			}				

			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Creation of iPSK Manager MySQL User Failed</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Creation of iPSK Manager MySQL User Successful</div>";
				$managerUserCreateFlag = true;
			}
			
			for($sqlCount = 0; $sqlCount < count($managerSqlPermissions); $sqlCount++){
				if(!$dbConnection->query($managerSqlPermissions[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Setting of iPSK Manager MySQL User Permissions Failed</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Setting of iPSK Manager MySQL User Permissions Successful</div>";
			}

			try {
				if(!$dbConnection->query($iseSqlUser[0])){
					$actionValid = false;
				}				
			}
			catch (Exception $e) {
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Error creating ISE ODBC User</div>";
				goto Bail;
			}

			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Creation of Cisco ISE MySQL User Failed</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Creation of Cisco ISE MySQL User Successful</div>";
				$iseUserCreateFlag = true;
			}

			for($sqlCount = 0; $sqlCount < count($iseSqlPermissions); $sqlCount++){
				if(!$dbConnection->query($iseSqlPermissions[$sqlCount])){
					$actionValid = false;
				}				
			}
			
			if(!$actionValid){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Setting of Cisco ISE MySQL User Permissions Failed</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Creation of Cisco ISE MySQL User Permissions Successful</div>";
			}			
			
			if(!file_put_contents("../supportfiles/include/config.php", $configurationFile)){
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Error creating config.php file</div>";
				goto Bail;
			}else{
				$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Successfully Created config.php</div>";
				$_SESSION['installSuccess'] = true;
				$_SESSION['installDetails'] = $installDetails;
				if($inContainer) {
					copy("../supportfiles/include/config.php","/opt/ipsk-manager/config.php");
				}
			}
			
			Bail:
			
			if($_SESSION['installSuccess']){
				$finalizeButton = '<input type="hidden" name="step" value="7"><input type="submit" id="btnFinalize" value="Finalize Installation" class="btn btn-primary shadow">';
				$finalizeButtonScript = '$("#btnFinalize").click( function(event) {coaTimer = setInterval("redirectToAdminPortal()", 5000);});';
				$finalErrorText = '<strong>Click Finalize Installation to finish the installation. <strong><br/> <p class="text-danger">Please Note: After you click finalize a file download will commence providing you with the Installation details and encryption key.</p>';
			}else{
				$finalizeButton = '';
				$finalizeButtonScript = '';
				
				try {
					$dbConnection->select_db("mysql");
				}
				catch (Error $e ) {}
				
				if($dbCreateFlag){
					$deleteDbQuery = sprintf("DROP DATABASE `%s`", $dbConnection->real_escape_string($_SESSION['databasename']));

					if(!$dbConnection->query($deleteDbQuery)){
						$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Database Installation Cleanup Failure</div>";
					}else{
						$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Database Installation Cleanup Successful</div>";
					}
				}
				
				if($managerUserCreateFlag){
					$deleteDbQuery = sprintf("DROP USER `%s`", $dbConnection->real_escape_string($_SESSION['databasename']));
					
					if(!$dbConnection->query($deleteDbQuery)){
						$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>Database User Cleanup Failure</div>";
					}else{
						$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>Database User Cleanup Successful</div>";
					}
				}
				
				if($iseUserCreateFlag){
					$deleteDbQuery = sprintf("DROP USER `%s`", $dbConnection->real_escape_string($_SESSION['iseusername']));
					
					if(!$dbConnection->query($deleteDbQuery)){
						$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>ISE Database User Cleanup Failure</div>";
					}else{
						$installProgress .= "<div><span style=\"color: #2d8c32\" data-feather=\"check-circle\"></span>ISE Database User Cleanup Successful</div>";
					}
				}		
		
				$installProgress .= "<div><span style=\"color: #ff0000\" data-feather=\"x-circle\"></span>iPSK Manager Installation Failure</div>";
					
				$finalErrorText = '<p class="text-danger"><strong>The installation failed to finish correctly. Please re-run the installation again.</strong></p>';
			}
		
			$installerOutput = <<< HTML
			<form method="POST" action="./installer.php">
				<div class="row">
					<div class="col-2"></div>
					<div class="col float-rounded mx-auto shadow-lg p-2 bg-white text-start"
						<strong>Installation Results:</strong>
						<div class="row">
							<div class="col-1"></div>
							<div class="col border border-primary text-start">$installProgress</div>
							<div class="col-1"></div>
						</div>
					</div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col">$finalErrorText</div>
					<div class="col-2"></div>
				</div>
				<div class="row">
					<div class="col"></div>
					<div class="col-3">$finalizeButton</div>
				</div>
			</form>
			<script type="text/javascript">
				$(function() {	
					feather.replace()
				});
				
				$finalizeButtonScript
				
				function redirectToAdminPortal(){
					window.location = "./";
				}
			</script>
HTML;

		}elseif($_POST['step'] == 7){
			if($_SESSION['installSuccess']){
				header('Content-Description: File Transfer');
				header('Content-Type: plain/text');
				header('Content-Disposition: attachment; filename=DONOTDELETE-iPSKManager-Install.txt'); 
				header('Content-Transfer-Encoding: text');
				header('Content-Length: '.strlen($_SESSION['installDetails']));
				echo $_SESSION['installDetails'];
				
				//Clear installer flag from the session as we have reached a successful point
				$_SESSION['identityPSKInstalling'] = false;
				unset($_SESSION['identityPSKInstalling']);
				
				session_destroy();
				unlink("installer.inc.php");
				unlink("installer.php");

				//If running within container with MySQL remove install user at end of install
				if (file_exists("/removeinstalluser.sh")) {
					exec("/bin/sudo /removeinstalluser.sh");
				}
				
				exit(0);
			}
		}
	}elseif ($upgrade) {
		if(copy("/opt/ipsk-manager/config.php","../supportfiles/include/config.php")) {
			unlink("installer.inc.php");
			unlink("installer.php");
			header("Location: ./");
			exit(0);

		} else {
			session_destroy();
			http_response_code(500);
			header("Location: 500.php");
			exit(0);
		}
	}else{
		$_SESSION['identityPSKInstalling'] = true;
		
		$installerOutput = <<< HTML
			<form method="POST" action="./installer.php">
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col text-start"><p>Welcome to the installer for iPSK Manager.  The installer will perform the intial setup and database population as well as create the proper credentials for both iPSK Manager and Cisco ISE for the ODBC integration.<br /><br />
					The installer is assuming that the installation will take place on a single server with a local MySQL database, for a non-standard deployment, please refer to the Installation Directions in the README File.<br /><br />
					The installer will setup the MySQL database and permissions required for proper operation.  It will also auto-generate the MySQL Database Passwords and Encryption Key for specific data stored within the Database.
					</p></div>
					<div class="col-2"></div>
				</div>
				<div class="row m-2">
					<div class="col-2"></div>
					<div class="col"><strong>Click Next to continue and check the system requirements.</strong></div>
					<div class="col-2"></div>
				</div>
				<div class="row">
					<div class="col"></div>
					<div class="col-2"><input type="hidden" name="step" value="1"><input type="submit" id="btn../installer.php" value="Next >" class="btn btn-primary shadow"></div>
				</div>
			</form>
			<script type="text/javascript">
			</script>

HTML;

	}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>iPSK Manager Installer</title>

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <link href="styles/installer.css" rel="stylesheet">
	
	<script type="text/javascript" src="scripts/jquery.min.js"></script>
	<script type="text/javascript" src="scripts/feather.min.js"></script>
	
  </head>

  <body class="text-center">
    <div class="float-rounded mx-auto shadow-lg p-2 bg-white window-install">
		<div class="mt-2 mb-4">
			<img src="images/iPSK-Logo.svg" width="108" height="57" />
		</div>
		<h1 class="h3 mt-2 mb-4 fw-normal">iPSK Manager Installer</h1>
		<?php print $installerOutput;?>
		<p class="mt-5 mb-0 text-muted">Copyright &copy; 2025 Cisco and/or its affiliates.</p>
	</div>
  </body>
</html>