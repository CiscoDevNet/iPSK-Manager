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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body">
	  <form class="needs-validation" novalidate>
			<label class="fw-bold" for="groupName">Username:</label>
			<div class="mb-3 input-group-sm fw-bold">
				<input type="text" class="form-control shadow" id="userName" value="{$internalUser['userName']}" readonly>
				<input type="hidden" validation-state="notempty" class="form-control shadow form-validation" id="userId" value="{$internalUser['id']}">
			</div>
			<label class="fw-bold" for="availablegroups">Available Internal Groups:</label>
			<div class="mb-3 input-group fw-bold">
				<select class="form-select shadow" id="availablegroups" multiple>
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
			<label class="fw-bold" for="memberof">Member Of:</label>
			<div class="mb-3 input-group fw-bold">
				<select class="form-select shadow form-validation" validation-state="required" id="memberof" multiple>
					$memberGroups	
				</select>
			</div>
		</form>
	  </div>
      <div class="modal-footer">
		<a id="updategroups" href="#" module="internalusers" sub-module="updategroups" role="button" class="btn btn-primary shadow">Update Membership</a>
        <button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;
	
	$("#manageGroupMembership").modal('show');
	
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
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('manageGroupMembership'));
			modal.hide();
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