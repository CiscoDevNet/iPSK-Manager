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
		$logMessage = "REQUEST:FAILURE[index_error]];ACTION:CAPTIVEPORTAL;CLIENTIP:".$_SERVER['REMOTE_ADDR'].";HOSTNAME:".$_SERVER['SERVER_NAME'].";TCPPORT:".$_SERVER['SERVER_PORT'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.png">

    <title><?php echo $portalSettings['portalName'];?></title>

    <!-- Bootstrap core CSS -->
    <link href="styles/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="styles/signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
    <div class="card mx-auto">
	<div class="card-header bg-primary mb-4">
  				<img src="images/ipsk-logo.gif" width="180" height="32" />
			</div>
			<div class="card-body">
				<h1 class="h4 mt-0 mb-4 fw-normal"><?php echo $portalSettings['portalName'];?></h1>
				<h2 class="h5 mt-0 mb-4 fw-normal">Please Login</h2>
			<?php
				if(isset($_GET['error'])) {
			?>
					<div class="col">
						<div class="alert alert-danger shadow" role="alert">Authentication Failed</div>
					</div>
			<?php
				}
			?>
				<form action="login.php?portalId=<?php echo $portalId;?>" method="post" class="form-signin">
					<label for="inputUsername" class="visually-hidden">Username</label>
					<input type="text" name="inputUsername" id="inputUsername" class="form-control mt-2 mb-3 shadow" placeholder="Username" required autofocus>
					<label for="inputPassword" class="visually-hidden">Password</label>
					<input type="password" name="inputPassword" id="inputPassword" class="form-control mt-2 mb-3 shadow" placeholder="Password" required>
					<button class="btn btn-primary btn-block mt-2 mb-3 shadow" id="loginbtn" type="submit">Sign in</button>
				</form>
			</div>
			<div class="card-footer bg-light">
				Copyright &copy; 2025 Cisco and/or its affiliates.
			</div>
		</div>
	</body>
</html>