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
	


	$ldapServers = $ipskISEDB->getLdapDirectories();
?>

<div class="card">
	<h4 class="text-center card-header bg-primary text-white pb-0 border-bottom-0">LDAP Identity Servers</h4>
	<h6 class="text-center card-header bg-primary text-white pt-0 border-top-0 fst-italic">Manage LDAP authentication and authorization servers</h6>
	<div class="card-header">
		<a id="addLdapServer" module="ldap" sub-module="add" class="btn btn-primary custom-link text-white" href="#" role="button">Add LDAP Server</a>
	</div>
	<div class="card-body">
    	<div class="tab-content" id="nav-tabContent">
			<table class="table table-hover">
  				<thead>
    				<tr>
      					<th scope="col">Connection Name</th>
      					<th scope="col">Domain Name</th>
	  					<th scope="col">Server</th>
	  					<th scope="col">Test</th>
		  				<th scope="col">View</th>
	  					<th scope="col">Edit</th>
	  					<th scope="col">Delete</th>
    				</tr>
  				</thead>
  				<tbody>
    <?php
		if($ldapServers){
			while($row = $ldapServers->fetch_assoc()) {			
				print '<tr>';
				print '<td>'.$row['adConnectionName'].'</td>';
				print '<td>'.$row['adDomain'].'</td>';
				print '<td>'.$row['adServer'].'</td>';
				print '<td><a class="epg-tableicons testserver" module="ldap" sub-module="test" row-id="'.$row['id'].'" href="#"><span data-feather="play-circle"></span></a></td>';
				print '<td><a class="epg-tableicons item-function" module="ldap" sub-module="view" row-id="'.$row['id'].'" href="#"><span data-feather="zoom-in"></span></a></td>';
				print '<td><a class="epg-tableicons item-function" module="ldap" sub-module="edit" row-id="'.$row['id'].'" href="#"><span data-feather="edit"></span></a></td>';
				print '<td><a class="epg-tableicons item-function" module="ldap" sub-module="delete" row-id="'.$row['id'].'" href="#"><span data-feather="x-square"></span></a></td>';
				print '</tr>';
			}
		}
	?>
  				</tbody>
			</table>
			<div id="popupcontent"></div>
    	</div>
	</div>
</div>
<script>
	$(function() {	
		feather.replace()
	});
	
	$(".testserver").click(function(event) {
		$.ajax({
			url: "ajax/getdata.php",
			
			data: {
				'data-command': $(this).attr('sub-module'),
				'data-set': $(this).attr('module'),
				id: $(this).attr('row-id')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				//$('#popupcontent').html(data);
				alert(data);
			},
			error: function (xhr, status) {
				//$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
		
		event.preventDefault();
	});
	
	$(".item-function").click(function(event) {
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				id: $(this).attr('row-id')
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
		
		event.preventDefault();
	});
	
	$(".custom-link").click(function(event) {
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module')
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
		
		event.preventDefault();
	});
</script>