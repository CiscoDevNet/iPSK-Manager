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
		print "<script>window.location = \"/index.php?portalId=$portalId\";</script>";
		die();
	}

	if(is_numeric($sanitizedInput['id']) && $sanitizedInput['id'] != 0 && $sanitizedInput['confirmaction']){
		$endpointPermissions = $ipskISEDB->getEndPointAssociationPermissions($sanitizedInput['id'],$_SESSION['authorizationGroups'], $_SESSION['portalSettings']['id']);
		
		if($endpointPermissions){
			if($endpointPermissions[0]['advancedPermissions'] & 64){
		
				$endPointAssociation = $ipskISEDB->getEndPointAssociationById($sanitizedInput['id']);
				
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
				$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORDELETE;METHOD:DELETE-ENDPOINT;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				$ipskISEDB->deleteEndpointAssociationbyId($sanitizedInput['id']);
				$ipskISEDB->deleteEndpointById($endPointAssociation['endpointId']);

				print <<<HTML
<script>
	window.location = "/manage.php?portalId=$portalId";
</script>
HTML;
			}
		}
	}else{
		print <<<HTML
<!-- Modal -->
<div class="modal fade" id="endpointdelete" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header shadow alert alert-danger">
				<h5 class="modal-title fw-bold" id="modalLongTitle">Delete Endpoint Association?</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
				  
				</button>
			</div>
			<div class="modal-body">
				<p class="h5">Are you sure you want to delete?</p>
			</div>
			<div class="modal-footer">
				<button type="button" module="endpoints" id="deleteBtn" class="btn btn-danger fw-bold shadow" data-bs-dismiss="modal">Yes</button>
				<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">No</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#endpointdelete").modal('show');

	$("#deleteBtn").click(function(){
		event.preventDefault();
		
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "/delete.php?portalId=$portalId",
			
			data: {
				confirmaction: 1,
				id: '{$sanitizedInput['id']}'
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			}
		});
		

	});

</script>
HTML;

	}
?>