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
	

	$authList = "";
	
	$authorizationTemplatesNames = $ipskISEDB->getAuthorizationTemplates();
	
	if($authorizationTemplatesNames){
		while($row = $authorizationTemplatesNames->fetch_assoc()) {
			$authList .= "<option value=\"".$row['id']."\">".$row['authzPolicyName']."</option>\n";
		}

		
	
		$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewepggroup" tabindex="-1" role="dialog" aria-labelledby="viewepggroupModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Endpoint Grouping</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
		<label class="fw-bold" for="epGroupName">iPSK Endpoint Group Name:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" id="epGroupName" validation-minimum-length="1" validation-maximum-length="25">
			<div class="invalid-feedback">Please enter a Endpoint Group Name (Max: 25 Characters)</div>
		</div>
		<label class="fw-bold" for="epGroupDescription">Description:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<input type="text" class="form-control shadow" id="epGroupDescription">
		</div>
		<div class="row">
			<div class="col">
				<div class="form-check">
					<input type="checkbox" class="form-check-input checkbox-update" base-value="1" value="0" id="notificationPermission">
					<label class="form-check-label" for="notificationPermission">Email Notifications</label>
				</div>
			</div>
		</div>	
		<label class="fw-bold" for="authzTemplate">Authorization Template:</label>
		<div class="mb-3 input-group-sm fw-bold">
			<select id="authzTemplate" class="form-select mt-2 mb-3 shadow">
				$authList
			</select>
		</div>
	  </div>
      <div class="modal-footer">
	  <a id="create" href="#" module="epgroup" sub-module="create" role="button" class="btn btn-primary shadow" data-bs-dismiss="modal">Create</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#viewepggroup").modal('show');

	$(function() {	
		feather.replace()
	});
	$("#showpassword").on('click', function(event) {
		event.preventDefault();
		if($("#presharedKey").attr('type') == "text"){
			$("#presharedKey").attr('type', 'password');
			$("#passwordfeather").attr('data-feather','eye');
			feather.replace();
		}else if($("#presharedKey").attr('type') == "password"){
			$("#presharedKey").attr('type', 'text');
			$("#passwordfeather").attr('data-feather','eye-off');
			feather.replace();
		}
	});
	
	$("#create").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		//$("#viewepggroup").modal({show: false});
		//$('.modal-backdrop').remove();
		//$("body").removeClass('modal-open');
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				epGroupName: $("#epGroupName").val(),
				epGroupDescription: $("#epGroupDescription").val(),
				authzTemplate: $("#authzTemplate").children("option:selected").val(),
				notificationPermission: $("#notificationPermission").val()
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
	
	$(".checkbox-update").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));		
		}else{
			$(this).attr('value', '0');
		}
		
	});
</script>
HTML;

	}else{
		$htmlbody = <<< HTML
		<div class="modal fade" id="noAuthorizationTemplates" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header shadow alert alert-danger">
						<h5 class="modal-title fw-bold" id="modalLongTitle">Missing Dependencies</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						  
						</button>
					</div>
					<div class="modal-body">
						<p class="h6">Authorization Template missing</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Ok</button>
					</div>
				</div>
			</div>
		</div>
		<script>
			var failure;
			
			$("#noAuthorizationTemplates").modal();

			$(function() {	
				feather.replace()
			});
		</script>
HTML;
	}

print $htmlbody;
?>