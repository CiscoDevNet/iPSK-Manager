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
	


if(is_numeric($sanitizedInput['id']) && $sanitizedInput['id'] != 0 && $sanitizedInput['confirmaction']){
	$endPointAssociation = $ipskISEDB->getEndPointAssociationById($sanitizedInput['id']);
	$ipskISEDB->activateEndpointAssociationbyId($endPointAssociation['endpointId']);

 	print <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'endpoints'
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
}else{
	print <<<HTML
<!-- Modal -->
<div class="modal fade" id="endpointactivate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header shadow alert alert-danger">
				<h5 class="modal-title fw-bold" id="modalLongTitle">Activate Endpoint's Access?</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
				  
				</button>
			</div>
			<div class="modal-body">
				<p class="h5">Are you sure you want to Activate the Endpoint?</p>
			</div>
			<div class="modal-footer">
				<button type="button" module="endpoints" sub-module="activate" id="activateBtn" class="btn btn-danger fw-bold shadow" data-bs-dismiss="modal">Yes</button>
				<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">No</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#endpointactivate").modal('show');

	$("#activateBtn").click(function(){
		event.preventDefault();
		
		//$('.modal-backdrop').remove();
		//$("body").removeClass('modal-open');
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
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