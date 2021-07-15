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
	
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: /index.php?portalId=".$portalId);
		die();
	}

	$pageSize = (isset($_GET['pageSize'])) ? $_GET['pageSize'] : 25;
	$currentPage = (isset($_GET['currentPage'])) ? $_GET['currentPage'] : 1;
	
	$queryDetails = "pageSize=$pageSize&currentPage=$currentPage";
	
	$page['endpoints'] = "";
	
	$temp = $_POST['id'];
	$sanitizedMembers = filter_var_array($temp, FILTER_VALIDATE_INT);
	
	if($sanitizedMembers){
		if(count($sanitizedMembers) > 0){
			if($sanitizedInput['confirmaction']){
				$endpointCount = 0;
				
				foreach($sanitizedMembers as $index => $id){
					$endpointIds[$endpointCount] = $id;
					$endpointCount++;
				}
				
				$endpointIds['count'] = $endpointCount;
				
				if($sanitizedInput['sub-module'] == "activate"){
					for($epIdx = 0; $epIdx < $endpointIds['count']; $epIdx++){
						$endpointPermissions = $ipskISEDB->getEndPointAssociationPermissions($endpointIds[$epIdx], $_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id']);
						if($endpointPermissions){
							if($endpointPermissions[0]['advancedPermissions'] & 32){
								$endPointAssociation = $ipskISEDB->getEndPointAssociationById($endpointIds[$epIdx]);
								$ipskISEDB->activateEndpointAssociationbyId($endPointAssociation['endpointId']);
							}
						}						
					}
					
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("sanitizedMembers"=>$sanitizedMembers));
					$logMessage = "REQUEST:SUCCESS;ACTION:BULKSPONSORACTIVATE;METHOD:ACTIVATE-ENDPOINT;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}elseif($sanitizedInput['sub-module'] == "suspend"){
					for($epIdx = 0; $epIdx < $endpointIds['count']; $epIdx++){
						$endpointPermissions = $ipskISEDB->getEndPointAssociationPermissions($endpointIds[$epIdx],$_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id']);
						if($endpointPermissions){
							if($endpointPermissions[0]['advancedPermissions'] & 16){
								$endPointAssociation = $ipskISEDB->getEndPointAssociationById($endpointIds[$epIdx]);
								$ipskISEDB->suspendEndpointAssociationbyId($endPointAssociation['endpointId']);
							}
						}	
					}
										
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("sanitizedMembers"=>$sanitizedMembers));
					$logMessage = "REQUEST:SUCCESS;ACTION:BULKSPONSORSUSPEND;METHOD:SUSPEND-ENDPOINT;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}elseif($sanitizedInput['sub-module'] == "delete"){
					for($epIdx = 0; $epIdx < $endpointIds['count']; $epIdx++){
						$endpointPermissions = $ipskISEDB->getEndPointAssociationPermissions($endpointIds[$epIdx],$_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id']);
						if($endpointPermissions){
							if($endpointPermissions[0]['advancedPermissions'] & 64){
								$endPointAssociation = $ipskISEDB->getEndPointAssociationById($endpointIds[$epIdx]);
																
								$ipskISEDB->deleteEndpointAssociationbyId($endpointIds[$epIdx]);
								$ipskISEDB->deleteEndpointById($endPointAssociation['endpointId']);
							}
						}							
					}

					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("sanitizedMembers"=>$sanitizedMembers));
					$logMessage = "REQUEST:SUCCESS;ACTION:BULKSPONSORDELETE;METHOD:DELETE-ENDPOINT;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}else{
							
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("sanitizedMembers"=>$sanitizedMembers));
					$logMessage = "REQUEST:FAILURE;ACTION:BULKSPONSOR;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);			
				}

				print <<<HTML
			
<script>
	window.location = "/manage.php?portalId=$portalId&$queryDetails";
</script>
HTML;
			}elseif($sanitizedInput['sub-module'] == "activate"){
				foreach($sanitizedMembers as $index => $id){
					$page['endpoints'] .= '<input type="hidden" class="ids" value="'.$id.'">';
				}
				
				print <<<HTML
<!-- Modal -->
<div class="modal fade" id="endpointactivate" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header shadow alert alert-danger">
				<h5 class="modal-title font-weight-bold" id="modalLongTitle">Activate Endpoint's Access?</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="h5">Are you sure you want to Activate the Endpoint?</p>
			</div>
			<div class="modal-footer">
				{$page['endpoints']}
				<button type="button" module="bulkupdate" sub-module="activate" id="activateBtn" class="btn btn-danger font-weight-bold shadow">Yes</button>
				<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">No</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#endpointactivate").modal({keyboard: false,backdrop: 'static',show: true});

	$("#activateBtn").click(function(){
		event.preventDefault();
		
		formData = new FormData();
		var multiSelect;

		formData.append('sub-module', $(this).attr('sub-module'));
		formData.append('confirmaction', 1);
		
		$(".ids").each(function() {
			if($(this).val() != 0){
				formData.append('id[]', $(this).val());
				multiSelect = true;
			}
		});

		if(multiSelect){
			$.ajax({
				url: "/" + $(this).attr('module') + ".php?portalId=$portalId&$queryDetails",

				data: formData,
				processData: false,
				contentType: false,
				type: "POST",
				dataType: "html",
				success: function (data) {
					$('#popupcontent').html(data);
				}
			});
		}
	});

</script>
HTML;
			}elseif($sanitizedInput['sub-module'] == "suspend"){
				foreach($sanitizedMembers as $index => $id){
					$page['endpoints'] .= '<input type="hidden" class="ids" value="'.$id.'">';
				}
				
				print <<<HTML
<!-- Modal -->
<div class="modal fade" id="endpointsuspend" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header shadow alert alert-danger">
				<h5 class="modal-title font-weight-bold" id="modalLongTitle">Suspend Endpoint's Access?</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="h5">Are you sure you want to Suspend the Endpoint?</p>
			</div>
			<div class="modal-footer">
				{$page['endpoints']}
				<button type="button" module="bulkupdate" sub-module="suspend" id="suspendBtn" class="btn btn-danger font-weight-bold shadow">Yes</button>
				<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">No</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#endpointsuspend").modal({keyboard: false,backdrop: 'static',show: true});

	$("#suspendBtn").click(function(){
		event.preventDefault();
		
		formData = new FormData();
		var multiSelect;

		formData.append('sub-module', $(this).attr('sub-module'));
		formData.append('confirmaction', 1);
		
		$(".ids").each(function() {
			if($(this).val() != 0){
				formData.append('id[]', $(this).val());
				multiSelect = true;
			}
		});

		if(multiSelect){
			$.ajax({
				url: "/" + $(this).attr('module') + ".php?portalId=$portalId&$queryDetails",

				data: formData,
				processData: false,
				contentType: false,
				type: "POST",
				dataType: "html",
				success: function (data) {
					$('#popupcontent').html(data);
				}
			});
		}
	});

</script>
HTML;
			}elseif($sanitizedInput['sub-module'] == "delete"){
				foreach($sanitizedMembers as $index => $id){
					$page['endpoints'] .= '<input type="hidden" class="ids" value="'.$id.'">';
				}
				
				print <<<HTML
<!-- Modal -->
<div class="modal fade" id="endpointdelete" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header shadow alert alert-danger">
				<h5 class="modal-title font-weight-bold" id="modalLongTitle">Delete Endpoint Association?</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="h5">Are you sure you want to delete?</p>
			</div>
			<div class="modal-footer">
				{$page['endpoints']}
				<button type="button" module="bulkupdate" sub-module="delete" id="deleteBtn" class="btn btn-danger font-weight-bold shadow">Yes</button>
				<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">No</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#endpointdelete").modal({keyboard: false,backdrop: 'static',show: true});

	$("#deleteBtn").click(function(){
		event.preventDefault();
		
		formData = new FormData();
		var multiSelect;

		formData.append('sub-module', $(this).attr('sub-module'));
		formData.append('confirmaction', 1);
		
		$(".ids").each(function() {
			if($(this).val() != 0){
				formData.append('id[]', $(this).val());
				multiSelect = true;
			}
		});

		if(multiSelect){
			$.ajax({
				url: "/" + $(this).attr('module') + ".php?portalId=$portalId&$queryDetails",

				data: formData,
				processData: false,
				contentType: false,
				type: "POST",
				dataType: "html",
				success: function (data) {
					$('#popupcontent').html(data);
				}
			});
		}
	});

</script>
HTML;
			}
		}
	}else{
		

	}
?>