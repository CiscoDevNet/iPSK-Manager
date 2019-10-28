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
	
	if(is_numeric($sanitizedInput['id']) && $sanitizedInput['id'] != 0 && $sanitizedInput['confirmaction'] && is_numeric($sanitizedInput['termLengthSeconds'])){
		
		if($_SESSION['extendEndpointMaxTerm'] == 0  && $sanitizedInput['termLengthSeconds'] == 0){
			$endPointAssociation = $ipskISEDB->extendEndpoint($sanitizedInput['id'], $sanitizedInput['termLengthSeconds'] + time(), $_SESSION['logonSID']);
		}elseif($_SESSION['extendEndpointMaxTerm'] != 0  && $sanitizedInput['termLengthSeconds'] == 0){
			$endPointAssociation = $ipskISEDB->extendEndpoint($sanitizedInput['id'], $_SESSION['extendEndpointMaxTerm'] + time(), $_SESSION['logonSID']);
		}elseif($_SESSION['extendEndpointMaxTerm'] >= $sanitizedInput['termLengthSeconds']){
			$endPointAssociation = $ipskISEDB->extendEndpoint($sanitizedInput['id'], $sanitizedInput['termLengthSeconds'] + time(), $_SESSION['logonSID']);
		}
		
		//LOG::Entry
		$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput));
		$logMessage = "REQUEST:SUCCESS;ACTION:SPONSOREXTEND;METHOD:EXTEND-ENDPOINT;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION["logonUsername"].";SID:".$_SESSION['logonSID'].";";
		$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
	
		unset($_SESSION['extendEndpointMaxTerm']);
		
		print <<<HTML
<script>
	window.location = "/manage.php?portalId=$portalId";
</script>
HTML;
	}else{
	
		$termLenOptions = '{"0":{"value":"86400","text":"1 Day"},"1":{"value":"172800","text":"2 Days"},"2":{"value":"259200","text":"3 Days"},"3":{"value":"345600","text":"4 Days"},"4":{"value":"432000","text":"5 Days"},"5":{"value":"518400","text":"6 Days"},"6":{"value":"604800","text":"1 Week"},"7":{"value":"1209600","text":"2 Weeks"},"8":{"value":"1814400","text":"3 Weeks"},"9":{"value":"2419200","text":"4 Weeks"},"10":{"value":"2592000","text":"1 Month"},"11":{"value":"5184000","text":"2 Months"},"12":{"value":"7776000","text":"3 Months"},"13":{"value":"10368000","text":"4 Months"},"14":{"value":"12960000","text":"5 Months"},"15":{"value":"15552000","text":"6 Months"},"16":{"value":"18144000","text":"7 Months"},"17":{"value":"20736000","text":"8 Months"},"18":{"value":"23328000","text":"9 Months"},"19":{"value":"25920000","text":"10 Months"},"20":{"value":"28512000","text":"11 Months"},"21":{"value":"31536000","text":"1 Year"},"22":{"value":"63072000","text":"2 Years"},"23":{"value":"94608000","text":"3 Years"},"24":{"value":"126144000","text":"4 Years"},"25":{"value":"157680000","text":"5 Years"},"26":{"value":"0","text":"No Expiration"},"count":27}';
		
		$pageTermLengthList = "";
		
		$termLenList = json_decode($termLenOptions,TRUE);
		
		$endpoint = $ipskISEDB->getEndpointByAssociationId($sanitizedInput['id']);
		
		for($count = 0; $count < $termLenList['count']; $count++){
			if($endpoint['termLengthSeconds'] == $termLenList[$count]['value']){
				$pageTermLengthList .= '<option value="'.$termLenList[$count]['value'].'" selected>'.$termLenList[$count]['text'].'</option>';
				$_SESSION['extendEndpointMaxTerm'] = $termLenList[$count]['value'];
				break;
			}else{
				$pageTermLengthList .= '<option value="'.$termLenList[$count]['value'].'">'.$termLenList[$count]['text'].'</option>';
			}
		}
	
		print <<<HTML
<!-- Modal -->
<div class="modal fade" id="extendendpoint" tabindex="-1" role="dialog" aria-labelledby="viewendpointModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Extend Endpoint Association</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<label class="font-weight-bold" for="macAddress">Endpoint MAC Address</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="macAddress" value="{$endpoint['macAddress']}" readonly>
		</div>
		<label class="font-weight-bold" for="fullName">Full Name</label>
		<div class="form-group input-group-sm font-weight-bold">
			<input type="text" class="form-control shadow" id="fullName" value="{$endpoint['fullName']}" readonly>
		</div>
		<div class="form-group input-group-sm font-weight-bold">
			<label class="font-weight-bold" for="termLengthSeconds">Extend Association by <span class="text-danger">(from today's date)</span>:</label>
			<select id="termLengthSeconds" class="form-control mt-2 mb-3 shadow">
				$pageTermLengthList
			</select>
		</div>
	  </div>
      <div class="modal-footer">
	    <input type="hidden" id="id" value="{$endpoint['endpointId']}">
 		<a id="extend" href="#" module="extend" role="button" class="btn btn-primary shadow" data-dismiss="modal">Extend</a>
        <button type="button" class="btn btn-secondary shadow" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	$("#extendendpoint").modal();

	$(function() {	
		feather.replace()
	});

	$("#extend").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "/extend.php?portalId=$portalId",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $("#id").val(),
				confirmaction: 1,
				termLengthSeconds: $("#termLengthSeconds").children("option:selected").val()
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