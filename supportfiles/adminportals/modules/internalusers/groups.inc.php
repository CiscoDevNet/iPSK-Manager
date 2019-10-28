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
	

	
	$availableGroups = "";
	$memberGroups = "";
	
	$id = filter_var($_POST['id'],FILTER_VALIDATE_INT);

	if($id > 0){

		$internalUser = $ipskISEDB->getInternalUserById($id);
		
		$memberof = $ipskISEDB->getInternalUserGroupMembership($id);
		
		$internalGroups = $ipskISEDB->getInternalGroups(0);
	
		if($memberof){
			while($row = $memberof->fetch_assoc()){
				$memberOfList[$row['id']] = $row['groupName'];
				$memberGroups .= '<option value="'.$row['id'].'">'.$row['groupName'].'</option>';
			}
		}
		
		if($internalGroups){
			while($row = $internalGroups->fetch_assoc()){
				if(!isset($memberOfList[$row['id']])){
					$availableGroups .= '<option value="'.$row['id'].'">'.$row['groupName'].'</option>';
				}
			
			}
		}
	
		$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="manageGroupMembership" tabindex="-1" role="dialog" aria-labelledby="manageGroupMembership" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Manage Group Membership</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
	  <form class="needs-validation" novalidate>
			<label class="font-weight-bold" for="groupName">Username:</label>
			<div class="form-group input-group-sm font-weight-bold">
				<input type="text" class="form-control shadow" id="userName" value="{$internalUser['userName']}" readonly>
				<input type="hidden" validation-state="notempty" class="form-control shadow form-validation" id="userId" value="{$internalUser['id']}">
			</div>
			<label class="font-weight-bold" for="availablegroups">Available Internal Groups:</label>
			<div class="form-group input-group font-weight-bold">
				<select class="form-control shadow" id="availablegroups" multiple>
					$availableGroups
				</select>
			</div>
			<hr />
			<div class="row text-center">
				<div class="col">
					<a id="addgroup" href="#" role="button" class="btn btn-primary shadow">Add Group</a>
				</div>
				<div class="col">
					<a id="removegroup" href="#" role="button" class="btn btn-primary shadow">Remove Group</a>
				</div>
			</div>
			<hr />
			<div class="row"></div>
			<label class="font-weight-bold" for="memberof">Member Of:</label>
			<div class="form-group input-group font-weight-bold">
				<select class="form-control shadow form-validation" validation-state="required" id="memberof" multiple>
					$memberGroups	
				</select>
			</div>
		</form>
	  </div>
      <div class="modal-footer">
		<a id="updategroups" href="#" module="internalusers" sub-module="updategroups" role="button" class="btn btn-primary shadow" data-dismiss="modal">Update Membership</a>
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#manageGroupMembership").modal({show: true, backdrop: true});
	
	$("#addgroup").click(function(){
		$("#availablegroups").find('option:selected').each(function( index ) {
			index = $(this).val(),
			text = $(this).html(),
			added = $('<option>', {value: index}),
			$("#memberof").append(added.html(text))
			$(this).remove()
		});
	});	
	
	$("#removegroup").click(function(){
		$("#memberof").find('option:selected').each(function( index ) {
			index = $(this).val(),
			text = $(this).html(),
			added = $('<option>', {value: index}),
			$("#availablegroups").append(added.html(text))
			$(this).remove()
		});
	});	

	$("#updategroups").click(function(){
		event.preventDefault();
		
		$('#memberof option').prop('selected', true);
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#userId").val(),
				memberof: $("#memberof").val()
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

		print $htmlbody;

	}
?>