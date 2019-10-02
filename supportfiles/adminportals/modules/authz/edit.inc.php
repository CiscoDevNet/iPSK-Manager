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
	
	$termLenOptions = '{"0":{"value":"0","text":"No Expiration"},"1":{"value":"86400","text":"1 Day"},"2":{"value":"172800","text":"2 Days"},"3":{"value":"259200","text":"3 Days"},"4":{"value":"345600","text":"4 Days"},"5":{"value":"432000","text":"5 Days"},"6":{"value":"518400","text":"6 Days"},"7":{"value":"604800","text":"1 Week"},"8":{"value":"1209600","text":"2 Weeks"},"9":{"value":"1814400","text":"3 Weeks"},"10":{"value":"2419200","text":"4 Weeks"},"11":{"value":"2592000","text":"1 Month"},"12":{"value":"5184000","text":"2 Months"},"13":{"value":"7776000","text":"3 Months"},"14":{"value":"10368000","text":"4 Months"},"15":{"value":"12960000","text":"5 Months"},"16":{"value":"15552000","text":"6 Months"},"17":{"value":"18144000","text":"7 Months"},"18":{"value":"20736000","text":"8 Months"},"19":{"value":"23328000","text":"9 Months"},"20":{"value":"25920000","text":"10 Months"},"21":{"value":"28512000","text":"11 Months"},"22":{"value":"31536000","text":"1 Year"},"23":{"value":"63072000","text":"2 Years"},"24":{"value":"94608000","text":"3 Years"},"25":{"value":"126144000","text":"4 Years"},"26":{"value":"157680000","text":"5 Years"},"count":27}';
	
	$pageTermLengthList = "";
	$pageiPSKType = "";
	$iPSKTypeFlag = "";
	$password = "";
	
	$termLenList = json_decode($termLenOptions,TRUE);
	
	$authorizationTemplate = $ipskISEDB->getAuthorizationTemplatesById($sanitizedInput['id']);
	
	if($authorizationTemplate['ciscoAVPairPSK'] == "*userrandom*"){
		$authorizationTemplate['ciscoAVPairPSK'] = "Randomly Chosen per User";
		$pageiPSKType .= '<option value="0">Unique PSK per Device</option>';
		$pageiPSKType .= '<option value="1" selected>Unique PSK per User</option>';
		$password = "Random";
		$readonlyFlag = " readonly";
		$passwordFeather = "slash";
		$pskModeFlag = "1";
		$keyType = '<option value="0">Common PSK</option><option value="1" selected>Random PSK</option>';
	}elseif($authorizationTemplate['ciscoAVPairPSK'] == "*devicerandom*"){
		$authorizationTemplate['ciscoAVPairPSK'] = "Randomly Chosen per Device";
		$pageiPSKType .= '<option value="0" selected>Unique PSK per Device</option>';
		$pageiPSKType .= '<option value="1">Unique PSK per User</option>';
		$password = "Random";
		$readonlyFlag = " readonly";
		$passwordFeather = "slash";
		$pskModeFlag = "1";
		$keyType = '<option value="0">Common PSK</option><option value="1" selected>Random PSK</option>';
	}else{
		$pageiPSKType .= '<option value="0">Unique PSK per Device</option>';
		$pageiPSKType .= '<option value="1">Unique PSK per User</option>';
		$password = $authorizationTemplate['ciscoAVPairPSK'];
		$readonlyFlag = "";
		$passwordFeather = "shuffle";
		$iPSKTypeFlag = " disabled";
		$pskModeFlag = "0";
		$keyType = '<option value="0" selected>Common PSK</option><option value="1">Random PSK</option>';
	}
	
	$authorizationTemplate['ciscoAVPairPSKMode'] = strtoupper($authorizationTemplate['ciscoAVPairPSKMode']);
	
	for($count = 0; $count < $termLenList['count']; $count++){
		if($authorizationTemplate['termLengthSeconds'] == $termLenList[$count]['value']){
			$pageTermLengthList .= '<option value="'.$termLenList[$count]['value'].'" selected>'.$termLenList[$count]['text'].'</option>';
		}else{
			$pageTermLengthList .= '<option value="'.$termLenList[$count]['value'].'">'.$termLenList[$count]['text'].'</option>';
		}
	}
	
$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="editauthztemplate" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Authorization Template</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="authzPolicyName">Authorization Template Name:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" id="authzPolicyName" value="{$authorizationTemplate['authzPolicyName']}">
			<div class="invalid-feedback">Please enter a valid Template Name!</div>
		</div>
		<label class="font-weight-bold" for="authzPolicyDescription">Description:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="authzPolicyDescription" value="{$authorizationTemplate['authzPolicyDescription']}">
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="termLengthSeconds">Access Term Length:</label>
			<select id="termLengthSeconds" class="form-control mt-2 mb-3 shadow">
				$pageTermLengthList
			</select>
		</div>
		<label class="font-weight-bold" for="pskLength">Pre-Shared Key Length:</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow form-validation" validation-state="required" id="pskLength" value="{$authorizationTemplate['pskLength']}">
			<div class="invalid-feedback">Please enter a PSK length greater than 8 and less than 64</div>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="keyType">Pre-Shared Key Type:</label>
			<select id="keyType" class="form-control mt-2 mb-3 shadow">
				$keyType
			</select>
		</div>
		<label class="font-weight-bold" for="ciscoAVPairPSK">Pre-Shared Key:</label>
		<div class="input-group input-group-sm mb-3">
			<div class="input-group-prepend shadow">
				<span class="input-group-text font-weight-bold" id="basic-addon1">ASCII</span>
			</div>
			<input type="text" id="ciscoAVPairPSK" class="form-control shadow form-validation" validation-state="required" validation-minimum-length="8" validation-maximum-length="64" value="$password" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true"$readonlyFlag>
			<div class="input-group-append shadow">
				<span class="input-group-text font-weight-bold" id="basic-addon1"><a id="generatepassword" data-command="generate" data-set="psk" href="#"><span id="passwordfeather" data-feather="$passwordFeather"></span></a></span>
			</div>
			<div class="invalid-feedback">Please enter a Valid Pre-Shared Key (Minimum length of 8 characters)</div>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="pskType">Random iPSK Type:</label>
			<select id="pskType" class="form-control mt-2 mb-3 shadow"$iPSKTypeFlag>
				$pageiPSKType
			</select>
		</div>
	  </div>
	  <input type="hidden" id="pskMode" value="$pskModeFlag">
      <div class="modal-footer">
		<input type="hidden" id="id" value="{$authorizationTemplate['id']}">
 		<a id="update" href="#" module="authz" sub-module="update" role="button" class="btn btn-primary shadow" data-dismiss="modal">Update</a>
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var failure;

	$("#editauthztemplate").modal();

	$(function() {	
		feather.replace()
	});
	
	$("#keyType").change(function() {
		event.preventDefault();
		if($("#ciscoAVPairPSK").val() == "Random"){
			$("#ciscoAVPairPSK").val('');
			$("#ciscoAVPairPSK").removeAttr('readonly');
			$("#pskType").attr('disabled','true');
			$("#pskMode").val('0');
			$("#passwordfeather").attr('data-feather','shuffle');
			feather.replace();
		}else if($("#ciscoAVPairPSK").val() != "Random"){
			$("#ciscoAVPairPSK").val('Random');
			$("#ciscoAVPairPSK").attr('readonly', 'readonly');
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
	
	$("#update").click(function(){
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
				id: $("#id").val(),
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