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
	


if(is_numeric($sanitizedInput['id']) && $sanitizedInput['id'] != 0 && $sanitizedInput['confirmaction']){
	$endPointAssociation = $ipskISEDB->getEndPointAssociationById($sanitizedInput['id']);
	$ipskISEDB->suspendEndpointAssociationbyId($endPointAssociation['endpointId']);

	//LOG::Entry
	$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
	$logMessage = "REQUEST:SUCCESS;ACTION:SPONSORSUSPEND;METHOD:SUSPEND-ENDPOINT;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
	$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);

	print <<<HTML
<script>
	window.location = "/manage.php?portalId=$portalId";
</script>
HTML;
}else{
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
				<button type="button" module="endpoints" id="suspendBtn" class="btn btn-danger font-weight-bold shadow">Yes</button>
				<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">No</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#endpointsuspend").modal({keyboard: false,backdrop: 'static',show: true});

	$("#suspendBtn").click(function(){
		event.preventDefault();
		
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "/suspend.php?portalId=$portalId",
			
			data: {
				confirmaction: 1,
				id: '{$sanitizedInput['id']}'
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
		

	});

</script>
HTML;

}
?>