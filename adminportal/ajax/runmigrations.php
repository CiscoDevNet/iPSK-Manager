<?php

/**
 *@license
 *
 *Copyright 2025 Cisco Systems, Inc. or its affiliates
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

	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Content-Type: application/json");

	//Core Components
	include("../../supportfiles/include/config.php");
	include("../../supportfiles/include/iPSKManagerFunctions.php");
	include("../../supportfiles/include/iPSKManagerDatabase.php");

	ipskSessionHandler();

	if(!ipskLoginSessionCheck()){
		session_destroy();
		http_response_code(401);
		print json_encode(Array("success" => false, "error" => "Session expired. Please sign in again."));
		die();
	}

	// Allow optional alternate DB credentials for privileged migrations (not stored).
	$altDbUser = (isset($_POST['dbuser']) && $_POST['dbuser'] != "") ? $_POST['dbuser'] : $dbUsername;
	$altDbPass = (isset($_POST['dbpass']) && $_POST['dbpass'] != "") ? $_POST['dbpass'] : $dbPassword;

	try{
		$ipskISEDB = new iPSKManagerDatabase($dbHostname, $altDbUser, $altDbPass, $dbDatabase);
		$ipskISEDB->set_encryptionKey($encryptionKey);
		$encryptionKey = "";

		$migrationResult = $ipskISEDB->runSchemaMigrations();

		if($migrationResult["success"]){
			$logMessage = "MIGRATION:SUCCESS;TARGET:".$migrationResult["targetVersion"].";APPLIED:".implode(",", $migrationResult["applied"]).";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $ipskISEDB->generateLogData(Array(), $migrationResult, Array()));
			print json_encode(Array("success" => true, "applied" => $migrationResult["applied"], "targetVersion" => $migrationResult["targetVersion"]));
		}else{
			http_response_code(500);
			$logMessage = "MIGRATION:FAILURE;TARGET:".$migrationResult["targetVersion"].";ERROR:".$migrationResult["error"].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $ipskISEDB->generateLogData(Array(), $migrationResult, Array()));
			print json_encode(Array("success" => false, "error" => $migrationResult["error"], "applied" => $migrationResult["applied"], "targetVersion" => $migrationResult["targetVersion"]));
		}
	}catch(mysqli_sql_exception $e){
		http_response_code(500);
		print json_encode(Array("success" => false, "error" => $e->getMessage()));
	}catch(Exception $e){
		http_response_code(500);
		print json_encode(Array("success" => false, "error" => $e->getMessage()));
	}
?>
