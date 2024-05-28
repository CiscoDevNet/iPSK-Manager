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

	
//Core Components
include("../supportfiles/include/config.php");
include("../supportfiles/include/iPSKManagerFunctions.php");
include("../supportfiles/include/iPSKManagerDatabase.php");

// Define your credentials
$valid_username = 'username';
$valid_password = 'password';

// Check if the user has provided credentials
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    // If not, send a 401 Unauthorized header and prompt the user for credentials
    header('WWW-Authenticate: Basic realm="My Website"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required';
    exit;
} else {
    // If credentials are provided, verify them
    if ($_SERVER['PHP_AUTH_USER'] !== $valid_username || $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
        // If credentials are invalid, send a 401 Unauthorized header and prompt the user for credentials again
        header('WWW-Authenticate: Basic realm="My Website"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Invalid credentials';
        exit;
    }
}

// If credentials are valid, continue with the page content
echo 'Welcome, ' . $_SERVER['PHP_AUTH_USER'] . '! You are authenticated.';



$ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);

$data = $ipskISEDB->getPxGridDirectEndpoints();
$rows = mysqli_fetch_all($data, MYSQLI_ASSOC);
print "<pre>".json_encode($rows,JSON_PRETTY_PRINT)."</pre>";