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
	
	
	//Clear Variables and set to blank
	$pageData['errorMessage'] = "";
    $pageData['createComplete'] = "";
	$pageData['endpointGroupList'] = "";
	$pageData['wirelessSSIDList'] = "";
	$pageData['endpointAssociationList'] = "";
	$pageData['hidePskFlag'] = " d-none";
	$randomPassword = "";
	$validInput = false;
	$deviceRandom = false;
	
	if($sanitizedInput['associationGroup'] != 0 && $sanitizedInput['wirelessSSID'] != 0 && $sanitizedInput['bulkImportType'] == 3 && $sanitizedInput['emailAddress'] != "" && $sanitizedInput['fullName'] != "" && $sanitizedInput['groupUuid'] != "") {	
		$validInput = true;
	}elseif($sanitizedInput['associationGroup'] != 0 && $sanitizedInput['wirelessSSID'] != 0 && $sanitizedInput['bulkImportType'] == 1 && $sanitizedInput['uploadkey'] != ""){
		$validInput = true;
	}
	
	if($validInput){
		$endpointGroupAuthorization = $ipskISEDB->getAuthorizationTemplatesbyEPGroupId($sanitizedInput['associationGroup']);
		
		if($endpointGroupAuthorization['ciscoAVPairPSK'] == "*devicerandom*"){
			$randomPassword = $ipskISEDB->generateRandomPassword($endpointGroupAuthorization['pskLength']);
			$deviceRandom = true;
			
		}elseif($endpointGroupAuthorization['ciscoAVPairPSK'] == "*userrandom*"){
			$userPsk = $ipskISEDB->getUserPreSharedKey($sanitizedInput['associationGroup'],$_SESSION['logonSID']);
						
			if(!$userPsk){
				$randomPassword = $ipskISEDB->generateRandomPassword($endpointGroupAuthorization['pskLength']);
				$randomPSKList = "psk=".$randomPassword;
			}else{
				$randomPassword = $userPsk;
				$randomPSKList = "psk=".$randomPassword;
			}
		}else{
			$randomPassword = $endpointGroupAuthorization['ciscoAVPairPSK'];
			$randomPSKList = "psk=".$randomPassword;
		}
		
		if($endpointGroupAuthorization['termLengthSeconds'] == 0){
			$duration = $endpointGroupAuthorization['termLengthSeconds'];
		}else{
			$duration = time() + $endpointGroupAuthorization['termLengthSeconds'];
		}
		
		if($sanitizedInput['bulkImportType'] == 1){
			$macaddressArray = $_SESSION['bulk-import'][$sanitizedInput['uploadkey']];
			
			unset($_SESSION['bulk-import'][$sanitizedInput['uploadkey']]);
			
			if($macaddressArray){
				if($macaddressArray['count'] > 0){
					for($entryIdx = 0; $entryIdx < $macaddressArray['count']; $entryIdx++){
						$macAddressList[$entryIdx] = $macaddressArray[$entryIdx]['macAddress'];
						$fullnameList[$entryIdx] = $macaddressArray[$entryIdx]['fullname'];
						$emailaddressList[$entryIdx] = $macaddressArray[$entryIdx]['emailaddress'];
						$descriptionList[$entryIdx] = $macaddressArray[$entryIdx]['description'];
						
						if($deviceRandom){
							$randomPassword = $ipskISEDB->generateRandomPassword($endpointGroupAuthorization['pskLength']);
							$deviceRandomPSK = "psk=".$randomPassword;
							$randomPSKList[$entryIdx] = $deviceRandomPSK;
						}
					}
				}
			}
		}elseif($sanitizedInput['bulkImportType'] == 3){
			$macaddressArray = json_decode($ipskISEERS->getEndPointsByEPGroup($sanitizedInput['groupUuid']), true);
			
			$count = 0;
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("macaddressArray"=>$macaddressArray));
				$logMessage = "BULKREQUEST:SUCCESS;ACTION:ISE-REQUEST;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			if($macaddressArray['SearchResult']['total'] > 0){
				foreach($macaddressArray['SearchResult']['resources'] as $entry){
					$macAddressList[$count] = $entry['name'];
					$count++;
				}
				
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("macAddressList"=>$macAddressList));
				$logMessage = "BULKREQUEST:SUCCESS;ACTION:BULKADD;METHOD:ISE-IMPORT;REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			}
		}
		
		if($sanitizedInput['bulkImportType'] == 1 && $macAddressList){
			$macAddressInsertID = $ipskISEDB->addBulkEndpoints($macAddressList, $fullnameList, $descriptionList, $emailaddressList, $randomPSKList, $duration, $_SESSION['logonSID']);
		}elseif($sanitizedInput['bulkImportType'] == 3 && $macAddressList){
			$macAddressInsertID = $ipskISEDB->addBulkEndpoints($macAddressList,$sanitizedInput['fullName'], $sanitizedInput['endpointDescription'], $sanitizedInput['emailAddress'], $randomPSKList, $duration, $_SESSION['logonSID']);
		}
		
		if($macAddressInsertID){
			if($macAddressInsertID['processed'] > 0){
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
				$logMessage = "BULKREQUEST:SUCCESS;ACTION:SPONSORCREATE;METHOD:ADD-ENDPOINT;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					
				if($ipskISEDB->addBulkEndpointAssociation($macAddressInsertID, $sanitizedInput['associationGroup'], $_SESSION['logonSID'])){
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
					$logMessage = "BULKREQUEST:SUCCESS;ACTION:SPONSORCREATE;METHOD:ADD-ENDPOINT-ASSOCIATION;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Import has completed successfully.</h3><h6></h6></div></div>";
					
					if(is_array($macAddressInsertID)){
						$insertAssociation = "";
						
						for($rowCount = 0; $rowCount < $macAddressInsertID['count']; $rowCount++){
							
							if($macAddressInsertID[$rowCount]['exists'] == true){
								$insertAssociation .= '<tr><td><div><span style="color: #ff0000" data-feather="x-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td><span class="text-danger">Endpoint Exists</span></td></tr>';
							}else{
								$insertAssociation .= '<tr><td><div><span style="color: #2d8c32" data-feather="check-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td>'.str_replace("psk=","", $macAddressInsertID[$rowCount]['psk']).'</td></tr>';
							}
						}
					}
	  
					$pageData['createComplete'] .= "<table class=\"table table-hover\"><thead><tr><th scope=\"col\">MAC Address</th><th scope=\"col\">Pre-Shared Key</th></tr></thead><tbody>$insertAssociation</tbody></table>";
					$randomPassword = "";
					$pageData['hidePskFlag'] = " d-none";
				}else{
					//LOG::Entry
					$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
					$logMessage = "BULKREQUEST:FAILURE[unable_to_create_endpoint_association];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
					$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					
					$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Import has failed.</h3><br><h5 class=\"text-danger\">(Error message: Unable to create associations for endpoints)</h5></div></div>";
					
					if(is_array($macAddressInsertID)){
						$insertAssociation = "";
						
						for($rowCount = 0; $rowCount < $macAddressInsertID['count']; $rowCount++){
							
							if($macAddressInsertID[$rowCount]['exists'] == true){
								$insertAssociation .= '<tr><td><div><span style="color: #ff0000" data-feather="x-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td><span class="text-danger">Endpoint Exists</span></td></tr>';
							}else{
								$insertAssociation .= '<tr><td><div><span style="color: #2d8c32" data-feather="check-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td>'.str_replace("psk=","", $macAddressInsertID[$rowCount]['psk']).'</td></tr>';
							}
						}
					}
	  
					$pageData['createComplete'] .= "<table class=\"table table-hover\"><thead><tr><th scope=\"col\">MAC Address</th><th scope=\"col\">Pre-Shared Key</th></tr></thead><tbody>$insertAssociation</tbody></table>";
					$randomPassword = "";
					$pageData['hidePskFlag'] = " d-none";
				}
			}else{
				//LOG::Entry
				$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
				$logMessage = "BULKREQUEST:FAILURE[endpoints_exists];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
				$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				
				$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Import has failed.</h3><h6 class=\"text-danger\">(Error message: Endpoints already exist)</h6></div></div>";
					
				if(is_array($macAddressInsertID)){
					$insertAssociation = "";
					
					for($rowCount = 0; $rowCount < $macAddressInsertID['count']; $rowCount++){
						
						if($macAddressInsertID[$rowCount]['exists'] == true){
							$insertAssociation .= '<tr><td><div><span style="color: #ff0000" data-feather="x-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td><span class="text-danger">Endpoint Exists</span></td></tr>';
						}else{
							$insertAssociation .= '<tr><td><div><span style="color: #2d8c32" data-feather="check-circle"></span>'.$macAddressInsertID[$rowCount]['macAddress'].'</div></td><td>'.str_replace("psk=","", $macAddressInsertID[$rowCount]['psk']).'</td></tr>';
						}
					}
				}
  
				$pageData['createComplete'] .= "<table class=\"table table-hover\"><thead><tr><th scope=\"col\">MAC Address</th><th scope=\"col\">Pre-Shared Key</th></tr></thead><tbody>$insertAssociation</tbody></table>";
				$randomPassword = "";
				$pageData['hidePskFlag'] = " d-none";
			}
		}else{
			//LOG::Entry
			$logData = $ipskISEDB->generateLogData(Array("sanitizedInput"=>$sanitizedInput), Array("macAddressList"=>$macAddressList));
			$logMessage = "BULKREQUEST:FAILURE[unable_to_create_endpoint];ACTION:SPONSORCREATE;MAC:".$sanitizedInput['macAddress'].";REMOTE-IP:".$_SERVER['REMOTE_ADDR'].";USERNAME:".$_SESSION['logonUsername'].";SID:".$_SESSION['logonSID'].";";
			$ipskISEDB->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
			$pageData['createComplete'] .= "<div class=\"row\"><div class=\"col\"><h3>The Endpoint Association has failed, please contact a support technician for assistance.</h3><h5 class=\"text-danger\">(Error message: Unable to create endpoint)</h5><hr>";

			$randomPassword = "";
			$pageData['hidePskFlag'] = " d-none";
		}
	}
	$htmlbody = <<< HTML
<!-- Modal -->
<div class="modal fade" id="bulkAddStatus" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Bulk Endpoint Import Results</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					
				</button>
			</div>
			<div class="modal-body">
				<div class="row text-start">
					<div class="col mx-auto shadow p-2 bg-white border border-primary">
						<div class="row m-auto text-start">
							{$pageData['createComplete']}
						</div>
						<div class="row">
							<div class="col{$pageData['hidePskFlag']}">
								<div class="input-group input-group-sm mb-3 shadow copied-popover" data-bs-animation="true" data-bs-container="body" data-bs-trigger="manual" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="Pre Shared Key has been Copied!">
									<div class="input-group-prepend">
										<span class="input-group-text fw-bold shadow" id="basic-addon1">Pre-Shared Key</span>
									</div>
									<input type="text" id="presharedKey" class="form-control shadow" process-value="$randomPassword" value="$randomPassword" aria-label="password" aria-describedby="basic-addon1" data-lpignore="true" readonly>
									<div class="input-group-append">
										<span class="input-group-text fw-bold shadow" id="basic-addon1"><a id="copyPassword" href="#" data-clipboard-target="#presharedKey"><span id="passwordfeather" data-feather="copy"></span></a></span>
									</div>
								</div>
								Click on the copy button to copy the Pre Shared Key to your Clipboard.
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="closeButton" class="btn btn-secondary shadow">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	$("#bulkAddStatus").modal({
		backdrop: 'static'
	});
	
	$("#bulkAddStatus").modal('show');

	$(function() {	
		feather.replace()
	});
		
	$("#closeButton").click(function(){
		event.preventDefault();
	
		//$("#bulkAddStatus").modal('hide');
		//$('body').removeClass('modal-open');
		//$('.modal-backdrop').remove();
	
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
	
	});
</script>
HTML;

print $htmlbody;

?>