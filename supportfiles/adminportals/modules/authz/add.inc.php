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
	
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="addauthztemplate" tabindex="-1" role="dialog" aria-labelledby="addauthztemplateModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Authorization Template</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="authzPolicyName">Authorization Template Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" id="authzPolicyName" value="">
			<div class="invalid-feedback">Please enter a valid Template Name!</div>
		</div>
		<label class="font-weight-bold" for="authzPolicyDescription">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="authzPolicyDescription" value="">
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="termLengthSeconds">Access Term Length:</label>
			<select id="termLengthSeconds" class="form-control mt-2 mb-3 shadow">
				<option value="0">No Expiration</option>
				<option value="86400">1 Day</option>
				<option value="172800">2 Days</option>
				<option value="259200">3 Days</option>
				<option value="345600">4 Days</option>
				<option value="432000">5 Days</option>
				<option value="518400">6 Days</option>
				<option value="604800" selected>1 Week</option>
				<option value="1209600">2 Weeks</option>
				<option value="1814400">3 Weeks</option>
				<option value="2419200">4 Weeks</option>
				<option value="2592000">1 Month</option>
				<option value="5184000">2 Months</option>
				<option value="7776000">3 Months</option>
				<option value="10368000">4 Months</option>
				<option value="12960000">5 Months</option>
				<option value="15552000">6 Months</option>
				<option value="18144000">7 Months</option>
				<option value="20736000">8 Months</option>
				<option value="23328000">9 Months</option>
				<option value="25920000">10 Months</option>
				<option value="28512000">11 Months</option>
				<option value="31536000">1 Year</option>
				<option value="63072000">2 Years</option>
				<option value="94608000">3 Years</option>
				<option value="126144000">4 Years</option>
				<option value="157680000">5 Years</option>
			</select>
		</div>
		<label class="font-weight-bold" for="pskLength">Pre-Shared Key Length:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" validation-minimum-length="1" validation-maximum-length="2" id="pskLength" value="8">
			<div class="invalid-feedback">Please enter a PSK length between 8 and 64</div>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="keyType">Pre-Shared Key Type:</label>
			<select id="keyType" class="form-control mt-2 mb-3 shadow">
				<option value="0">Common PSK</option>
				<option value="1">Random PSK</option>
			</select>
		</div>
		<label class="font-weight-bold" for="ciscoAVPairPSK">Pre-Shared Key:</label>
		<div class="input-group input-group-sm mb-3">
			<div class="input-group-prepend shadow">
				<span class="input-group-text font-weight-bold" id="basic-addon1">ASCII</span>
			</div>
			<input type="text" id="ciscoAVPairPSK" class="form-control shadow form-validation" validation-state="required" validation-minimum-length="8" validation-maximum-length="64" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true">
			<div class="input-group-append shadow">
				<span class="input-group-text font-weight-bold" id="basic-addon1"><a id="generatepassword" data-command="generate" data-set="psk" href="#"><span id="passwordfeather" data-feather="shuffle"></span></a></span>
			</div>
			<div class="invalid-feedback">Please enter a Valid Pre-Shared Key (Key Length must be betwee 8-64 Characters Long)</div>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="pskType">Random iPSK Type:</label>
			<select id="pskType" class="form-control mt-2 mb-3 shadow" disabled>
				<option value="0">Unique PSK per Device</option>
				<option value="1">Unique PSK per User</option>
			</select>
		</div>
	  </div>
	  <input type="hidden" id="pskMode" value="0">
      <div class="modal-footer">
		<a id="create" href="#" module="authz" sub-module="create" role="button" class="btn btn-primary shadow" data-dismiss="modal">Create</a>
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;

	$("#addauthztemplate").modal();

	$(function() {	
		feather.replace()
	});
	
	$("#keyType").change(function() {
		event.preventDefault();
		if($("#ciscoAVPairPSK").val() == "Random"){
			$("#ciscoAVPairPSK").val('');
			$("#ciscoAVPairPSK").removeAttr('readonly');
			$("#ciscoAVPairPSK").attr('validation-state', 'required');
			$("#pskType").attr('disabled','true');
			$("#pskMode").val('0');
			$("#passwordfeather").attr('data-feather','shuffle');
			feather.replace();
		}else if($("#ciscoAVPairPSK").val() != "Random"){
			$("#ciscoAVPairPSK").val('Random');
			$("#ciscoAVPairPSK").attr('readonly', 'readonly');
			$("#ciscoAVPairPSK").attr('validation-state', 'special');
			$("#pskType").removeAttr('disabled');
			$("#pskMode").val('1');
			$("#passwordfeather").attr('data-feather','slash');
			feather.replace();
		}
	});
	
	$("#generatepassword").on('click', function(event) {
		if($("#ciscoAVPairPSK").val() != "Random"){
			event.preventDefault();

			$.ajax({
				url: "ajax/getdata.php",
				data: {
					'data-command': $(this).attr('data-command'),
					'data-set': $(this).attr('data-set'),
					'pskLength': $("#pskLength").val()
				},
				type: "POST",
				dataType: "text",
				success: function (data) {
					$("#ciscoAVPairPSK").val( data );
				}
			});
		}
	});
	
	$("#create").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		$("#addauthztemplate").modal({show: false});
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				authzPolicyName: $("#authzPolicyName").val(),
				authzPolicyDescription: $("#authzPolicyDescription").val(),
				termLengthSeconds: $("#termLengthSeconds").children("option:selected").val(),
				ciscoAVPairPSK: $("#ciscoAVPairPSK").val(),
				pskLength: $("#pskLength").val(),
				pskMode: $("#pskMode").val(),
				pskType: $("#pskType").val()
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
?>