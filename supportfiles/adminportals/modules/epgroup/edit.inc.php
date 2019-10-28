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
	
	$authList = "";
	
	$endPointGroup = $ipskISEDB->getEndpointGroupById($sanitizedInput['id']);
	$authorizationTemplatesNames = $ipskISEDB->getAuthorizationTemplates();
	
	if($endPointGroup['notificationPermission'] == 1){
		$endPointGroup['notificationPermissionCheck'] = " checked";
	}else{
		$endPointGroup['notificationPermissionCheck'] = "";
	}
	
	if($endPointGroup['ciscoAVPairPSK'] == "*userrandom*"){
		$endPointGroup['ciscoAVPairPSK'] = "Randomly Chosen per User";
	}elseif($endPointGroup['ciscoAVPairPSK'] == "*devicerandom*"){
		$endPointGroup['ciscoAVPairPSK'] = "Randomly Chosen per Device";
	}

	if($authorizationTemplatesNames){
		while($row = $authorizationTemplatesNames->fetch_assoc()) {
			if($endPointGroup['authzTemplateId'] == $row['id']){
				$authList .= "<option value=\"".$row['id']."\" selected>".$row['authzPolicyName']."</option>\n";
			}else{
				$authList .= "<option value=\"".$row['id']."\">".$row['authzPolicyName']."</option>\n";
			}
		}
	}
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="editepggroup" tabindex="-1" role="dialog" aria-labelledby="editepggroupModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Endpoint Grouping</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="epGroupName">iPSK Endpoint Group Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" id="epGroupName" value="{$endPointGroup['groupName']}">
		</div>
		<label class="font-weight-bold" for="epGroupDescription">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="epGroupDescription" value="{$endPointGroup['description']}">
		</div>
		<div class="form-row">
			<div class="col">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input checkbox-update" base-value="1" value="{$endPointGroup['notificationPermission']}" id="notificationPermission"{$endPointGroup['notificationPermissionCheck']}>
					<label class="custom-control-label" for="notificationPermission">Email Notifications</label>
				</div>
			</div>
		</div>	
		<label class="font-weight-bold" for="authzTemplate">Authorization Template:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<select id="authzTemplate" class="form-control mt-2 mb-3 shadow">
				$authList
			</select>
		</div>
	  </div>
      <div class="modal-footer">
	  <input type="hidden" id="id" value="{$endPointGroup['id']}">
	  <a id="update" href="#" module="epgroup" sub-module="update" role="button" class="btn btn-primary shadow" data-dismiss="modal">Update</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#editepggroup").modal();

	$(function() {	
		feather.replace()
	});
	
	$("#update").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		$("#viewepggroup").modal({show: false});
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
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

print $htmlbody;
?>