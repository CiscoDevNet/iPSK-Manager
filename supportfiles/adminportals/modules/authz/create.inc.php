<?php
if (!function_exists('ipskAdminSessionCheck') || !ipskAdminSessionCheck()) {
    http_response_code(403);
    return;
}

	
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
    if (!isset($sanitizedInput['termLengthSeconds']) || !is_int($sanitizedInput['termLengthSeconds']) || $sanitizedInput['termLengthSeconds'] < 0 || $sanitizedInput['termLengthSeconds'] > 157680000) {
        http_response_code(400); print '<p class="text-danger">Access term must be between no expiration and five years.</p>'; return;
    }

	if($sanitizedInput['authzPolicyName'] != ""){

		if($sanitizedInput['pskMode'] == 0) {
			if(ipskValidPsk($sanitizedInput['ciscoAVPairPSK'])){
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

		if ($failure || !ipskValidPskPolicy($psk ?? null, $sanitizedInput['pskLength'] ?? null)) {
            http_response_code(400); print '<p class="text-danger">Use 8–63 printable ASCII characters or 64 hexadecimal digits. Generated length must be 8–63.</p>'; return;
        }
        if(!isset($sanitizedInput['vlan'])) {
			$sanitizedInput['vlan'] = '';
		}

		if(!isset($sanitizedInput['dacl'])) {
			$sanitizedInput['dacl'] = '';
		}

		if(!$failure){
			$ipskISEDB->addAuthorizationTemplate($sanitizedInput['authzPolicyName'], $sanitizedInput['authzPolicyDescription'], $psk, $sanitizedInput['termLengthSeconds'], $sanitizedInput['pskLength'], $sanitizedInput['vlan'], $sanitizedInput['dacl'], $_SESSION['logonSID']);
			
		}
	}

	print $htmlbody;


?>