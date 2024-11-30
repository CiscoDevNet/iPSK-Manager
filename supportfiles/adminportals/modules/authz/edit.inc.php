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
		$minLength = '6';
		$readonlyFlag = " readonly";
		$passwordFeather = "slash";
		$pskModeFlag = "1";
		$keyType = '<option value="0">Common PSK</option><option value="1" selected>Random PSK</option>';
	}elseif($authorizationTemplate['ciscoAVPairPSK'] == "*devicerandom*"){
		$authorizationTemplate['ciscoAVPairPSK'] = "Randomly Chosen per Device";
		$pageiPSKType .= '<option value="0" selected>Unique PSK per Device</option>';
		$pageiPSKType .= '<option value="1">Unique PSK per User</option>';
		$password = "Random";
		$minLength = '6';
		$readonlyFlag = " readonly";
		$passwordFeather = "slash";
		$pskModeFlag = "1";
		$keyType = '<option value="0">Common PSK</option><option value="1" selected>Random PSK</option>';
	}else{
		$pageiPSKType .= '<option value="0">Unique PSK per Device</option>';
		$pageiPSKType .= '<option value="1">Unique PSK per User</option>';
		$password = $authorizationTemplate['ciscoAVPairPSK'];
		$readonlyFlag = "";
		$minLength = '8';
		$passwordFeather = "refresh-cw";
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
<div class="modal fade" id="editauthztemplate" tabindex="-1" role="dialog" aria-labelledby="authzEditModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Edit Authorization Template</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					
				</button>
			</div>
			<div class="modal-body">
				<label class="fw-bold" for="authzPolicyName">Authorization Template Name:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow form-validation" validation-state="required" id="authzPolicyName" value="{$authorizationTemplate['authzPolicyName']}">
					<div class="invalid-feedback">Please enter a valid Template Name!</div>
				</div>
				<label class="fw-bold" for="authzPolicyDescription">Description:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow" id="authzPolicyDescription" value="{$authorizationTemplate['authzPolicyDescription']}">
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="termLengthSeconds">Access Term Length:</label>
					<select id="termLengthSeconds" class="form-select mt-2 mb-3 shadow">
						$pageTermLengthList
					</select>
				</div>
				<label class="fw-bold" for="pskLength">Pre-Shared Key Length:</label>
				<div class="mb-3 input-group-sm fw-bold">
					<input type="text" class="form-control shadow form-validation" validation-state="required" id="pskLength" value="{$authorizationTemplate['pskLength']}">
					<div class="invalid-feedback">Please enter a PSK length greater than 8 and less than 64</div>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="keyType">Pre-Shared Key Type:</label>
					<select id="keyType" class="form-select mt-2 mb-3 shadow">
						$keyType
					</select>
				</div>
				<label class="fw-bold" for="ciscoAVPairPSK">Pre-Shared Key:</label>
				<div class="input-group input-group-sm mb-3">
					<div class="input-group-prepend shadow">
						<span class="input-group-text fw-bold" id="basic-addon1">ASCII</span>
					</div>
					<input type="text" id="ciscoAVPairPSK" class="form-control shadow form-validation" validation-state="required" validation-minimum-length="$minLength" validation-maximum-length="64" value="$password" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true"$readonlyFlag>
					<div class="input-group-append shadow">
						<span class="input-group-text fw-bold" id="basic-addon1"><a id="generatepassword" data-command="generate" data-set="psk" href="#"><span id="passwordfeather" data-feather="$passwordFeather"></span></a></span>
					</div>
					<div class="invalid-feedback">Please enter a Valid Pre-Shared Key (Minimum length of 8 characters)</div>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="pskType">Random iPSK Type:</label>
					<select id="pskType" class="form-select mt-2 mb-3 shadow"$iPSKTypeFlag>
						$pageiPSKType
					</select>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="vlan">VLAN (optional): <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Setting a VLAN is optional but doing so will place the VLAN attribute on any endpoint that this authorization template is assigned to. This can then be used in dynamic ISE authorization profile responses." data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					<div class="mb-3 input-group-sm fw-bold">
						<input type="text" class="form-control shadow" id="vlan" value="{$authorizationTemplate['vlan']}">
					</div>
				</div>
				<div class="mb-3 input-group-sm fw-bold">
					<label class="fw-bold" for="dacl">dACL (optional): <a class="d-inline-block" data-bs-toggle="tooltip" title="" data-bs-original-title="Setting a dACL is optional but doing so will place the dACL attribute on any endpoint that this authorization template is assigned to. This can then be used in dynamic ISE authorization profile responses." data-bs-placement="right"><i data-feather="help-circle"></i></a></label>
					<div class="mb-3 input-group-sm fw-bold">
						<input type="text" class="form-control shadow" id="dacl" value="{$authorizationTemplate['dacl']}">
					</div>
				</div>
				<div class="row">
					<div class="col m-2 shadow p-2 bg-white border border-primary">
						<label class="fw-bold" for="viewPermission">Pre-Shared Key, VLAN, dACL Change Options:</label>	
						<div class="mb-3">
							<div class="form-check">
								<input type="checkbox" class="form-check-input checkbox-update" base-value="1" value="" id="fullAuthZUpdate">
								<label class="form-check-label" for="fullAuthZUpdate">Reset <strong>ALL</strong> Associated Endpoint's Pre-Shared Key</label>
							</div>
							<div class="form-check">
								<input type="checkbox" class="form-check-input checkbox-update" base-value="1" value="" id="fullAuthZUpdateVLANdACL">
								<label class="form-check-label" for="fullAuthZUpdateVLANdACL">Reset <strong>ALL</strong> Associated Endpoint's VLANs and dACLs</label>
							</div>
							<small id="viewPermissionBlock" class="form-text text-danger fw-bold">WARNING: This will reset <strong>ALL</strong> Endpoint's Pre-Shared Keys, VLAN's, and dACL's associated to this policy!!!</small>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" id="pskMode" value="$pskModeFlag">
				<input type="hidden" id="id" value="{$authorizationTemplate['id']}">
				<a id="update" href="#" module="authz" sub-module="update" role="button" class="btn btn-primary shadow">Update</a>
				<button type="button" class="btn btn-secondary shadow" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	var failure;

	$("#editauthztemplate").modal('show');

	$(function() {	
		feather.replace()

		var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
		var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  			return new bootstrap.Tooltip(tooltipTriggerEl)
		})
	});
	
	$("#keyType").change(function() {
		event.preventDefault();
		if($("#ciscoAVPairPSK").val() == "Random"){
			$("#ciscoAVPairPSK").val('');
			$("#ciscoAVPairPSK").removeAttr('readonly');
			$("#ciscoAVPairPSK").attr('validation-minimum-length','8');
			$("#pskType").attr('disabled','true');
			$("#pskMode").val('0');
			$("#passwordfeather").attr('data-feather','refresh-cw');
			feather.replace();
		}else if($("#ciscoAVPairPSK").val() != "Random"){
			$("#ciscoAVPairPSK").val('Random');
			$("#ciscoAVPairPSK").attr('readonly', 'readonly');
			$("#ciscoAVPairPSK").attr('validation-minimum-length','6');
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
	
	$(".checkbox-update").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));		
		}else{
			$(this).attr('value', '0');
		}
	});
	
	$("#update").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		} else {
			const modal = bootstrap.Modal.getInstance(document.getElementById('editauthztemplate'));
			modal.hide();
		}

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
				pskType: $("#pskType").val(),
				fullAuthZUpdate: $("#fullAuthZUpdate").val(),
				fullAuthZUpdateVLANdACL: $("#fullAuthZUpdateVLANdACL").val(),
				vlan: $("#vlan").val(),
				dacl: $("#dacl").val()
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