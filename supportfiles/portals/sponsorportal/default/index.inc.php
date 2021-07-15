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

	

	if(isset($_GET['error'])){
		$pageData['errorMessage'] = "Authentication Failure";
		
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("pageData"=>$pageData), Array("portalSettings"=>$portalSettings));
		$logMessage = "REQUEST:FAILURE[index_error]];ACTION:SPONSORPORTAL;CLIENTIP:".$_SERVER['REMOTE_ADDR'].";HOSTNAME:".$_SERVER['SERVER_NAME'].";TCPPORT:".$_SERVER['SERVER_PORT'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
		
		print <<< HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.png">

    <title>{$portalSettings['portalName']}</title>

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="styles/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <div class="float-rounded mx-auto shadow-lg p-2 bg-white">
		<form action="login.php?portalId=$portalId" method="post" class="form-signin">
			<div class="mt-2 mb-4">
				<img src="images/iPSK-Logo.svg" width="108" height="57" />
			</div>
			<h1 class="h3 mt-2 mb-4 font-weight-normal">{$portalSettings['portalName']}</h1>
			<h2 class="h6 mt-2 mb-3 font-weight-normal">Please Login</h2>
			<div class="col">
				<div class="alert alert-danger shadow" role="alert">{$pageData['errorMessage']}</div>
			</div>
			<label for="inputEmail" class="sr-only">Username</label>
			<input type="text" name="inputUsername" id="inputUsername" class="form-control mt-2 mb-3 shadow" placeholder="Username" required autofocus>
			<label for="inputPassword" class="sr-only">Password</label>
			<input type="password" name="inputPassword" id="inputPassword" class="form-control mt-2 mb-3 shadow" placeholder="Password" required>
			<button class="btn btn-lg btn-primary btn-block mt-2 mb-3" type="submit">Sign in</button>
		</form>
		<p class="mt-5 mb-0 text-muted">Copyright &copy; 2019 Cisco and/or its affiliates.</p>
	</div>
  </body>
</html>
HTML;

	}else{
		print <<< HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.png">

    <title>{$portalSettings['portalName']}</title>

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="styles/signin.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <div class="float-rounded mx-auto shadow-lg p-2 bg-white">
		<form action="login.php?portalId=$portalId" method="post" class="form-signin">
			<div class="mt-2 mb-4">
				<img src="images/iPSK-Logo.svg" width="108" height="57" />
			</div>
			<h1 class="h3 mt-2 mb-4 font-weight-normal">{$portalSettings['portalName']}</h1>
			<h2 class="h6 mt-2 mb-3 font-weight-normal">Please Login</h2>
			<label for="inputUsername" class="sr-only">Username</label>
			<input type="text" name="inputUsername" id="inputUsername" class="form-control mt-2 mb-3 shadow" placeholder="Username" required autofocus>
			<label for="inputPassword" class="sr-only">Password</label>
			<input type="password" name="inputPassword" id="inputPassword" class="form-control mt-2 mb-3 shadow" placeholder="Password" required>
			<button class="btn btn-lg btn-primary btn-block mt-2 mb-3 shadow" id="loginbtn" type="submit">Sign in</button>
		</form>
		<p class="mt-5 mb-0 text-muted">Copyright &copy; 2019 Cisco and/or its affiliates.</p>
	</div>
  </body>
  <script type="text/javascript" src="scripts/jquery.min.js"></script>
    <script type="text/javascript">

	</script>
</html>
HTML;

	}


?>