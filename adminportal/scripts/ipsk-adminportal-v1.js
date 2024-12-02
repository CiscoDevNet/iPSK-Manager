/*
Copyright 2021 Cisco Systems, Inc. or its affiliates

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/


$(document).ready(function() {
	
	feather.replace()
	
	$('[data-toggle="popover"]').popover()
	
	$.ajax({
		url: "ajax/getmodule.php",
		
		data: {
			module: "dashboard"
		},
		type: "POST",
		dataType: "html",
		success: function (data) {
			$('#mainContent').html(data);
		},
		error: function (xhr, status) {
			//$('#mainContent').html(data);
		},
		complete: function (xhr, status) {
			//$('#mainContent').slideDown('slow')
		}
	})
	
	$(".sideNav").click(function(event) {
		$("#currentMenuItem").remove();
		$("a.sideNav.active").removeClass("active");
		$(this).addClass("active");
		//$(this).append("<span id=\"currentMenuItem\" class=\"sr-only\">(current)</span>");
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module')
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
		
		event.preventDefault();
	});
	
	$("#signOut").click(function(event) {
		$.ajax({
			url: "logoff.php",
			
			data: {
				logoff: true
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				window.location = "./";
			}
		});
		
		event.preventDefault();
	});

	if($("#newInstallDetected").length){
		$("#newInstallDetected").modal('show');
	}

	if($("#databaseUpdateDetected").length){
		$("#databaseUpdateDetected").modal('show');
	}
});	

function formFieldValidation(){
	var validationFailure;

	validationFailure = false;
	minValidateFailure = false;
	
	//Form Validation Object Loop
	$('.form-validation').each(function() {
		if($(this).attr('validation-state') == 'required'){
			if($(this).attr('validation-minimum-length') || $(this).attr('validation-maximum-length')){
				if($(this).attr('validation-minimum-length')){
					//Check Field Min Length
					if($(this).val().length < $(this).attr('validation-minimum-length')){
						$(this).removeClass('is-valid');
						$(this).addClass('is-invalid');
						validationFailure = true;
						minValidateFailure = true;
					}else{
						$(this).removeClass('is-invalid');
						$(this).addClass('is-valid');
					}
				}

				if($(this).attr('validation-maximum-length')){
					//Check Field Max Length
					if($(this).attr('validation-maximum-length') && !minValidateFailure){
						if($(this).val().length > $(this).attr('validation-maximum-length')){
							$(this).removeClass('is-valid');
							$(this).addClass('is-invalid');
							validationFailure = true;
						}else{
							$(this).removeClass('is-invalid');
							$(this).addClass('is-valid');
						}
					}
				}
			}else{
				//Check Field not Empty
				if($(this).val() == ''){
					$(this).removeClass('is-valid');
					$(this).addClass('is-invalid');
					validationFailure = true;
				}else{
					$(this).removeClass('is-invalid');
					$(this).addClass('is-valid');
				}
			}
		}else if($(this).attr('validation-state') == 'special'){
			
			//Check Field not Empty
			if($(this).val() == ''){
				$(this).removeClass('is-valid');
				$(this).addClass('is-invalid');
				validationFailure = true;
			}else if($(this).val() == 'Random'){
				$(this).removeClass('is-invalid');
				$(this).addClass('is-valid');
			}else{
				$(this).removeClass('is-invalid');
				$(this).addClass('is-valid');
			}
		}else if($(this).attr('validation-state') == 'notempty'){
			//Check Field not Empty
			if($(this).val() == ''){
				$(this).removeClass('is-valid');
				$(this).addClass('is-invalid');
				validationFailure = true;
			}else{
				$(this).removeClass('is-invalid');
				$(this).addClass('is-valid');
			}
		}else{
			$(this).removeClass('is-invalid');
			$(this).addClass('is-valid');
		}
	});
	
	return validationFailure;
}

function macAddressFormat(userInput) {
	//Filter out invalid characters from string and convert to Uppercase
	var fieldContents = $(userInput).val().replace(/[g-z]|[G-Z]|\W|\s/g, "").toUpperCase();
	
	//Format value to comply with MAC Address Format
	var formatted = fieldContents.replace(/(.{2})/g, "$1:");
	
	//Output base on length to truncate excess ':'
	if(formatted.length >= 18){
		$(userInput).val(formatted.substring(0, 17));
	}else{
		$(userInput).val(formatted);
	}
}