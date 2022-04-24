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
	
	$enablePskEdit = $ipskISEDB->getGlobalSetting("advanced-settings","enable-portal-psk-edit");
	
	//Add up the permissions for the group.
	if($enablePskEdit){
		$permissions = $sanitizedInput['portalPskEditCheck'] + $sanitizedInput['bulkCreateCheck'] + $sanitizedInput['suspendCheck'] + $sanitizedInput['unsuspendCheck'] + $sanitizedInput['extendCheck'] + $sanitizedInput['deleteCheck'] + $sanitizedInput['editCheck'] + $sanitizedInput['createCheck'] + $sanitizedInput['viewPassCheck'] + $sanitizedInput['viewPermission'] + $sanitizedInput['resetPskCheck'];
	}else{
		$permissions = $sanitizedInput['bulkCreateCheck'] + $sanitizedInput['suspendCheck'] + $sanitizedInput['unsuspendCheck'] + $sanitizedInput['extendCheck'] + $sanitizedInput['deleteCheck'] + $sanitizedInput['editCheck'] + $sanitizedInput['createCheck'] + $sanitizedInput['viewPassCheck'] + $sanitizedInput['viewPermission'] + $sanitizedInput['resetPskCheck'];
	}
	
	if($permissions > 0 && $permissions < 4095){
		
		if($sanitizedInput['sponsorGroupName'] != "" && isset($_POST['endpointGroupMembers']) && isset($_POST['wirelessNetworkMembers']) && isset($_POST['authorizationGroups'])){
			if(is_array($_POST['endpointGroupMembers']) && is_array($_POST['wirelessNetworkMembers'])){
						
				$sponsorGroupId = $ipskISEDB->addSponsorGroup($sanitizedInput['sponsorGroupName'], $sanitizedInput['sponsorGroupDescription'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['maxDevices'], $sanitizedInput['sponsorGroupType'], $_SESSION['logonSID']);

				$temp = $_POST['endpointGroupMembers'];
				$sanitizedEPGMembers = filter_var_array($temp,FILTER_VALIDATE_INT);

				$ipskISEDB->addSponsorGroupEPGMapping($sanitizedEPGMembers, $sponsorGroupId, $_SESSION['logonSID']);
					
				$temp = $_POST['wirelessNetworkMembers'];
				$sanitizedWirelessMembers = filter_var_array($temp,FILTER_VALIDATE_INT);

				$ipskISEDB->addSponsorGroupSSIDMapping($sanitizedWirelessMembers, $sponsorGroupId, $_SESSION['logonSID']);
				
				$temp = $_POST['authorizationGroups'];
				$sanitizedauthorizationGroups = filter_var_array($temp,FILTER_VALIDATE_INT);
				
				$ipskISEDB->addSponsorInternalGroupMapping($sanitizedauthorizationGroups, $sponsorGroupId, $permissions, $_SESSION['logonSID']);
				
			}else{
				if(isset($_POST['endpointGroupMembers']) && isset($_POST['wirelessNetworkMembers']) && ($_POST['wirelessNetworkMembers'] > 0) && ($_POST['endpointGroupMembers'] > 0)){
					
					$sponsorGroupId = $ipskISEDB->addSponsorGroup($sanitizedInput['sponsorGroupName'], $sanitizedInput['sponsorGroupDescription'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['sponsorGroupAuthType'], $sanitizedInput['maxDevices'], $sanitizedInput['sponsorGroupType'], $_SESSION['logonSID']);
					
					$temp = $_POST['endpointGroupMembers'];
					$sanitizedEPGMembers = filter_var($temp,FILTER_VALIDATE_INT);

					$ipskISEDB->addSponsorGroupEPGMapping($sanitizedEPGMembers, $sponsorGroupId, $_SESSION['logonSID']);
						
					$temp = $_POST['wirelessNetworkMembers'];
					$sanitizedWirelessMembers = filter_var($temp,FILTER_VALIDATE_INT);

					$ipskISEDB->addSponsorGroupSSIDMapping($sanitizedWirelessMembers, $sponsorGroupId, $_SESSION['logonSID']);
					
					$temp = $_POST['authorizationGroups'];
					$sanitizedauthorizationGroups = filter_var_array($temp,FILTER_VALIDATE_INT);
					
					$ipskISEDB->addSponsorInternalGroupMapping($sanitizedauthorizationGroups, $sponsorGroupId, $permissions, $_SESSION['logonSID']);
				}
			}
		}
	}
	
	print $htmlbody;
?>