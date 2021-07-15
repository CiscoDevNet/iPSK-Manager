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
	

	
$htmlbody = <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'portals'
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

	if($sanitizedInput['portalName'] != "" && isset($_POST['sponsorGroups']) && isset($_POST['sponsorPortalType'])){
		if(is_array($_POST['sponsorGroups'])){
			
			$ipskISEDB->updateSponsorPortal($sanitizedInput['id'], $sanitizedInput['portalName'], $sanitizedInput['description'], $sanitizedInput['sponsorPortalType'], $sanitizedInput['hostname'], $sanitizedInput['tcpPort'], $sanitizedInput['authDirectory'], $_SESSION['logonSID']);
			
			$temp = $_POST['sponsorGroups'];
			$sanitizedSponsorGroups = filter_var_array($temp,FILTER_VALIDATE_INT);
				
			$ipskISEDB->updateSponsorGroupPortalMapping($sanitizedSponsorGroups, $sanitizedInput['id'], $_SESSION['logonSID']);
			
		}
	}

	print $htmlbody;
?>