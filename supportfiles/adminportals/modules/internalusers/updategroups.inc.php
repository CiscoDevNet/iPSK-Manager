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
	

	
$htmlbody = <<<HTML
<script>
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: 'internalusers'
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
	
	if(isset($_POST['id']) && isset($_POST['memberof']) && is_array($_POST['memberof'])){
			
		$temp = $_POST['memberof'];
		$sanitizedInternalGroups = filter_var_array($temp, FILTER_VALIDATE_INT);
		$sanitizedId = filter_var($_POST['id'], FILTER_VALIDATE_INT);
			
		$ipskISEDB->updateInternalUserGroupMapping($sanitizedInternalGroups, $sanitizedId, $_SESSION['logonSID']);
	}

	print $htmlbody;
?>