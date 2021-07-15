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
	$ipskISEDB->deleteAuthTemplateById($sanitizedInput['id']);

	print <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'authz',
				test: 'Test'
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
<div class="modal fade" id="authtemplatedelete" tabindex="-1" role="dialog" aria-labelledby="authtemplatedeleteModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header shadow alert alert-danger">
				<h5 class="modal-title font-weight-bold" id="exampleModalLongTitle">Delete Authorization Template?</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="h5">Are you sure you want to delete?</p>
			</div>
			<div class="modal-footer">
				<button type="button" module="authz" id="deleteBtn" class="btn btn-danger font-weight-bold shadow">Yes</button>
				<button type="button" class="btn btn-secondary shadow" data-dismiss="modal">No</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#authtemplatedelete").modal({keyboard: false,backdrop: 'static',show: true});

	$("#deleteBtn").click(function(){
		event.preventDefault();
		
		$("#authtemplatedelete").modal({show: false});
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': 'delete',
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