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

	class iPSKManagerMigration {
		private $dbConnection;
		private $dbName;
		private $migrationPath;
		private $replacements;

		function __construct($dbConnection, $dbName, $migrationPath, $replacements = Array()) {
			$this->dbConnection = $dbConnection;
			$this->dbName = $dbName;
			$this->migrationPath = $migrationPath;
			$this->replacements = $replacements;
		}

		function set_replacements($replacements = Array()){
			$this->replacements = $replacements;
		}

		function getMigrations(){
			$migrations = Array();

			if(!is_dir($this->migrationPath)){
				return $migrations;
			}

			$migrationFiles = glob($this->migrationPath . "/v*__*.sql");

			if($migrationFiles){
				foreach($migrationFiles as $filePath){
					$fileName = basename($filePath);
					if(preg_match('/v([0-9]+)__.+\\.sql$/', $fileName, $matches)){
						$migrations[] = Array(
							"version" => (int)$matches[1],
							"filename" => $fileName,
							"path" => $filePath
						);
					}
				}

				usort($migrations, function($a, $b){
					if($a["version"] == $b["version"]){
						return strcmp($a["filename"], $b["filename"]);
					}
					return ($a["version"] < $b["version"]) ? -1 : 1;
				});
			}

			return $migrations;
		}

		function getPendingMigrations($currentVersion, $targetVersion = null){
			$pending = Array();
			$migrations = $this->getMigrations();

			foreach($migrations as $migration){
				if($migration["version"] > $currentVersion){
					if($targetVersion == null || $migration["version"] <= $targetVersion){
						$pending[] = $migration;
					}
				}
			}

			return $pending;
		}

		function executeMigration($migration){
			$result = Array(
				"success" => false,
				"error" => "",
				"filename" => $migration["filename"],
				"version" => $migration["version"]
			);

			if(!isset($migration["path"]) || !file_exists($migration["path"])){
				$result["error"] = "Migration file not found.";
				return $result;
			}

			$sql = file_get_contents($migration["path"]);

			if($sql === false){
				$result["error"] = "Unable to read migration file.";
				return $result;
			}

			if(isset($this->replacements) && is_array($this->replacements)){
				$sql = strtr($sql, $this->replacements);
			}

			$statements = $this->splitSqlStatements($sql);

			foreach($statements as $statement){
				$trimmedStatement = trim($statement);

				if($trimmedStatement == ""){
					continue;
				}

				if(!$this->dbConnection->query($trimmedStatement)){
					$result["error"] = "Error executing statement in ".$migration["filename"].": ".$this->dbConnection->error;
					return $result;
				}

				while($this->dbConnection->more_results() && $this->dbConnection->next_result()) {
					// Flush multi_query buffers for stored routines
				}
			}

			$result["success"] = true;
			return $result;
		}

		private function splitSqlStatements($sql){
			$delimiter = ";";
			$statements = Array();
			$currentStatement = "";

			$lines = preg_split('/\\r\\n|\\r|\\n/', $sql);

			foreach($lines as $line){
				if(preg_match('/^\\s*DELIMITER\\s+(.+)$/i', $line, $matches)){
					$delimiter = $matches[1];
					continue;
				}

				$currentStatement .= $line . "\n";

				$trimmed = rtrim($currentStatement);

				if($delimiter != "" && substr($trimmed, -strlen($delimiter)) === $delimiter){
					$statements[] = substr($trimmed, 0, -strlen($delimiter));
					$currentStatement = "";
				}
			}

			$remainingStatement = trim($currentStatement);

			if($remainingStatement != ""){
				$statements[] = $remainingStatement;
			}

			return $statements;
		}
	}
?>
