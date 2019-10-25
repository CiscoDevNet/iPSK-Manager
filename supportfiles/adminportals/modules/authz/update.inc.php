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
	

	
	$htmlbody = <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'authz'
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#mainContent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
</script>
HTML;

	$failure = false;

	if($sanitizedInput['authzPolicyName'] != "" && $sanitizedInput['termLengthSeconds'] < 157680000 && $sanitizedInput['ciscoAVPairPSK'] != ""){
		if(isset($sanitizedInput['pskLength'])){
			$sanitizedInput['pskLength'] = ($sanitizedInput['pskLength'] == 0) ? 8 : $sanitizedInput['pskLength'];
			$sanitizedInput['pskLength'] = ($sanitizedInput['pskLength'] < 8) ? 8 : $sanitizedInput['pskLength'];
			$sanitizedInput['pskLength'] = ($sanitizedInput['pskLength'] > 64) ? 64 : $sanitizedInput['pskLength'];
		}
		
		if($sanitizedInput['pskMode'] == 0){
			if(strlen($sanitizedInput['ciscoAVPairPSK']) > 7){
				$psk = $sanitizedInput['ciscoAVPairPSK'];
			}else{
				$failure = true;
			}
		}else{
			if($sanitizedInput['pskType'] == 0){
				$psk = "*devicerandom*";
			}else{
				$psk = "*userrandom*";
			}
		}
		
		if(!$failure){
			$ipskISEDB->updateAuthorizationTemplate($sanitizedInput['id'], $sanitizedInput['authzPolicyName'], $sanitizedInput['authzPolicyDescription'], $psk, $sanitizedInput['termLengthSeconds'], $sanitizedInput['pskLength'], $_SESSION['logonSID']);
		
			if($sanitizedInput['fullAuthZUpdate'] == true){
				$endpointsToUpdate = $ipskISEDB->getEndpointsByAuthZPolicy($sanitizedInput['id']);
				
				if($endpointsToUpdate){
					if($sanitizedInput['pskMode'] == 0){
						for($itemCount = 0; $itemCount < $endpointsToUpdate['count']; $itemCount++){
							$ipskISEDB->updateEndpointPsk($endpointsToUpdate[$itemCount]['id'], "psk=".$psk);
						}
					}else{
						for($itemCount = 0; $itemCount < $endpointsToUpdate['count']; $itemCount++){
							$ipskISEDB->updateEndpointPsk($endpointsToUpdate[$itemCount]['id'], "psk=".generatePsk($sanitizedInput['pskLength']));
						}
					}			
				}
			}
		}
	}

	print $htmlbody;

?>