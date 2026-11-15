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

            // Validate every replacement before executing even the first statement.
            if (preg_match('/\{\{[A-Z_]+\}\}/', $sql, $missing)) {
                $routineTokens = ['{{ISE_DB_DEFINER}}'=>'iPSK_AttributeFetch', '{{PLAIN_MAC_DEFINER}}'=>'iPSK_AuthMACPlain'];
                $result['error'] = isset($routineTokens[$missing[0]])
                    ? 'Cannot determine the existing ISE database owner (DEFINER) for '.$routineTokens[$missing[0]].'. Retry with alternate DB credentials that can inspect and replace routines. If the routine is missing and the remaining ISE routine owners differ or are unavailable, have your DBA restore it from backup or configure the confirmed ISE_DB_USERNAME. No procedure was changed by this migration.'
                    : 'Missing migration configuration: '.$missing[0];
                return $result;
            }
            $statements = $this->splitSqlStatements($sql);
            $backups = []; $dropped = []; $missingRoutines = [];
            try {
                // Prove CREATE/definer privileges using a temporary routine before dropping a working one.
                foreach ($statements as $statement) {
                    if (!preg_match('/CREATE\s+(?:DEFINER\s*=\s*`(?:``|[^`])+`@`(?:``|[^`])+`\s+)?PROCEDURE\s+`([^`]+)`/i', $statement, $match)) continue;
                    $name = $match[1];
                    try {
                        $existing = $this->dbConnection->query('SHOW CREATE PROCEDURE `'.str_replace('`','``',$name).'`');
                        if (!$existing && ($this->dbConnection->errno ?? 0) !== 1305) {
                            throw new RuntimeException('Cannot inspect the existing routine: '.$name.'. Use alternate DB credentials with permission to read and replace routines.');
                        }
                    } catch (Throwable $error) {
                        if ((int)$error->getCode() !== 1305) throw new RuntimeException('Cannot inspect the existing routine: '.$name.'. Use alternate DB credentials with permission to read and replace routines. '.$error->getMessage(), (int)$error->getCode(), $error);
                        $existing = false;
                    }
                    if ($existing) {
                        $row = $existing->fetch_assoc();
                        if (!$row || empty($row['Create Procedure'])) throw new RuntimeException('Cannot read the existing routine definition for recovery: '.$name.'. Use alternate DB credentials with permission to read and replace routines.');
                        $backups[$name] = $row['Create Procedure'];
                    } else {
                        // Error 1305 can also conceal a routine from a restricted user.
                        // Never DROP without a readable backup. CREATE will safely fail
                        // if it actually exists, including a concurrent creation.
                        $missingRoutines[$name] = true;
                    }
                    $temporary = 'ipsk_preflight_'.bin2hex(random_bytes(8));
                    $candidate = preg_replace('/(PROCEDURE\s+)`'.preg_quote($name,'/').'`/i', '$1`'.$temporary.'`', $statement, 1);
                    if (!$this->dbConnection->query($candidate)) throw new RuntimeException('Routine preflight failed: '.$this->dbConnection->error);
                    if (!$this->dbConnection->query('DROP PROCEDURE `'.$temporary.'`')) throw new RuntimeException('Cannot clean up routine preflight: '.$this->dbConnection->error);
                }
                foreach ($statements as $statement) {
                    if (trim($statement) === '') continue;
                    if (preg_match('/DROP PROCEDURE(?: IF EXISTS)? `([^`]+)`/i', $statement, $drop) && isset($missingRoutines[$drop[1]])) continue;
                    if (!$this->dbConnection->query($statement)) throw new RuntimeException($this->dbConnection->error);
                    if (preg_match('/DROP PROCEDURE(?: IF EXISTS)? `([^`]+)`/i',$statement,$match)) $dropped[$match[1]]=true;
                    if (preg_match('/CREATE\s+(?:DEFINER\s*=\s*`(?:``|[^`])+`@`(?:``|[^`])+`\s+)?PROCEDURE\s+`([^`]+)`/i',$statement,$match)) unset($dropped[$match[1]]);
                    while ($this->dbConnection->more_results() && $this->dbConnection->next_result()) {}
                }
            } catch (Throwable $error) {
                $result['error'] = 'Migration failed: '.$error->getMessage();
                if ((int)$migration['version'] === 13 && (int)$error->getCode() === 1062) {
                    $result['error'] .= ' Ambiguous legacy OpenLDAP ownership: review the schema 13 README procedure and assign verified endpoint owners before retrying. No ownership was changed by this preflight.';
                }
                if (in_array((int)$error->getCode(), [1044,1045,1142,1227,1370], true) && !str_contains($result['error'], 'alternate DB credentials')) {
                    $result['error'] .= ' Use alternate DB credentials with permission to read and replace routines while preserving their original DEFINER.';
                }
                try { $this->dbConnection->rollback(); } catch (Throwable $rollbackError) {}
                foreach ($dropped as $name=>$unused) {
                    if (!isset($backups[$name])) continue;
                    try {
                        if (!$this->dbConnection->query($backups[$name])) throw new RuntimeException($this->dbConnection->error);
                    } catch (Throwable $restoreError) {
                        $result['error'] .= '; recovery failed for '.$name.': '.$restoreError->getMessage();
                        $result['recoverySql'][$name] = $backups[$name];
                    }
                }
                return $result;
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
