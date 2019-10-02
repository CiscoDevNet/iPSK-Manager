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
				module: 'sponsorgroups'
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
	
	//Add up the permissions for the group.
	$permissions = $sanitizedInput['suspendCheck'] + $sanitizedInput['unsuspendCheck'] + $sanitizedInput['extendCheck'] + $sanitizedInput['deleteCheck'] + $sanitizedInput['editCheck'] + $sanitizedInput['createCheck'] + $sanitizedInput['viewPassCheck'] + $sanitizedInput['viewPermission'] + $sanitizedInput['resetPskCheck'];
	
	if($permissions > 0 && $permissions < 1024){
		
		if($sanitizedInput['sponsorGroupName'] != "" && isset($_POST['endpointGroupMembers']) && isset($_POST['wirelessNetworkMembers']) && isset($_POST['authorizationGroups'])){
			if(is_array($_POST['endpointGroupMembers']) && is_array($_POST['wirelessNetworkMembers'])){
						
				$sponsorGroupId = $ipskISEDB->updateSponsorGroup($sanitizedInput['id'], $sanitizedInput['sponsorGroupName'], $sanitizedInput['sponsorGroupDescription'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['maxDevices'], $sanitizedInput['sponsorGroupType'], $_SESSION['logonSID']);

				$temp = $_POST['endpointGroupMembers'];
				$sanitizedEPGMembers = filter_var_array($temp,FILTER_VALIDATE_INT);

				$ipskISEDB->updateSponsorGroupEPGMapping($sanitizedEPGMembers, $sanitizedInput['id'], $_SESSION['logonSID']);
					
				$temp = $_POST['wirelessNetworkMembers'];
				$sanitizedWirelessMembers = filter_var_array($temp,FILTER_VALIDATE_INT);

				$ipskISEDB->updateSponsorGroupSSIDMapping($sanitizedWirelessMembers, $sanitizedInput['id'], $_SESSION['logonSID']);
				
				$temp = $_POST['authorizationGroups'];
				$sanitizedauthorizationGroups = filter_var_array($temp,FILTER_VALIDATE_INT);
				
				$ipskISEDB->updateSponsorInternalGroupMapping($sanitizedauthorizationGroups, $sanitizedInput['id'], $permissions, $_SESSION['logonSID']);
				
			}else{
				if(isset($_POST['endpointGroupMembers']) && isset($_POST['wirelessNetworkMembers']) && ($_POST['wirelessNetworkMembers'] > 0) && ($_POST['endpointGroupMembers'] > 0)){
					
					$sponsorGroupId = $ipskISEDB->updateSponsorGroup($sanitizedInput['id'], $sanitizedInput['sponsorGroupName'], $sanitizedInput['sponsorGroupDescription'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['maxDevices'], $sanitizedInput['sponsorGroupType'], $_SESSION['logonSID']);
					
					$temp = $_POST['endpointGroupMembers'];
					$sanitizedEPGMembers = filter_var($temp,FILTER_VALIDATE_INT);

					$ipskISEDB->updateSponsorGroupEPGMapping($sanitizedEPGMembers, $sanitizedInput['id'], $_SESSION['logonSID']);
						
					$temp = $_POST['wirelessNetworkMembers'];
					$sanitizedWirelessMembers = filter_var($temp,FILTER_VALIDATE_INT);

					$ipskISEDB->updateSponsorGroupSSIDMapping($sanitizedWirelessMembers, $sanitizedInput['id'], $_SESSION['logonSID']);
					
					$temp = $_POST['authorizationGroups'];
					$sanitizedauthorizationGroups = filter_var_array($temp,FILTER_VALIDATE_INT);
					
					$ipskISEDB->updateSponsorInternalGroupMapping($sanitizedauthorizationGroups, $sanitizedInput['id'], $permissions, $_SESSION['logonSID']);
				}
			}
		}
	}
	
	print $htmlbody;
?>