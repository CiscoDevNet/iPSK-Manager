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
	
	ipskSessionHandler();
	
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.png">

    <title>iPSK Manager for Cisco ISE</title>

    <!-- Bootstrap core CSS -->
    <link href="404/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="404/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <div class="float-rounded mx-auto shadow-lg p-2 bg-white">
			<div class="mt-2 mb-4">
				<img src="images/iPSK-Logo.svg" width="108" height="57" />
			</div>
			<h1 class="h3 mt-2 mb-4 font-weight-normal">iPSK Manager for Cisco ISE</h1>
			
			<div class="col">
				<div class="alert alert-warning shadow" role="alert"><h2 class="h2 mt-2 mb-3 font-weight-bold">Error 404</h2><br>The requested page was not found.</div>
			</div>
		<p class="mt-5 mb-0 text-muted">&copy; 2019 Cisco Systems, Inc.</p>
	</div>
  </body>
</html>