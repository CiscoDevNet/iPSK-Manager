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
	
	if(!ipskLoginSessionCheck()){
		$portalId = $_GET['portalId'];
		$_SESSION = null;
		session_destroy();
		header("Location: index.php?portalId=".$portalId);
		die();
	}
	
	//Define Initial Variables
	$uploadFile = $_FILES['csvFile']['tmp_name'];
	
	if($sanitizedInput['uploadkey'] != ""){
		unset($_SESSION['bulk-import'][$sanitizedInput['uploadkey']]);
	}
	
	//Check File Extension for a CSV File
	if(strtolower(substr($_FILES['csvFile']['name'],strlen($_FILES['csvFile']['name']) - 4, 4)) == ".csv"){
		//Import file data from the uploaded file
		$tempfileUploadContents = file_get_contents($uploadFile);
		
		//Quickly Trim the Contents of the file
		$fileUploadContents = trim($tempfileUploadContents);
		
		//Check if file contents are valid
		if($fileUploadContents != ""){		
			$fileContentsArray = explode("\n",$fileUploadContents);
		
			if($fileContentsArray){
				if(count($fileContentsArray) > 1){
					$returnResult['recordsprocessed'] = count($fileContentsArray) - 1;
					
					//Trim Entry Contents
					$tempHeaderContents = trim($fileContentsArray[0]);
					
					//Filter Header Row Contents
					$fileUploadContents = filter_var($tempHeaderContents,FILTER_VALIDATE_REGEXP, Array('options'=> Array('regexp' => "/^(?:((macaddress)|(fullname)|(emailaddress)|(description)|(,)){7})$/i")));
					
					if($fileUploadContents != ""){
						
						//Validate CSV Headers and map the fields accordingly
						$csvHeader = explode(",", $fileUploadContents);
						
						if($csvHeader){
							if(count($csvHeader) == 4){
								$validHeaders = 0;
								
								foreach($csvHeader as $entry => $data){
									if(strtolower(trim($data)) == "macaddress"){ $validHeaders++; $fieldMapping['macaddress'] = $entry;}
									if(strtolower(trim($data)) == "fullname"){ $validHeaders++; $fieldMapping['fullname'] = $entry;}
									if(strtolower(trim($data)) == "emailaddress"){ $validHeaders++; $fieldMapping['emailaddress'] = $entry;}
									if(strtolower(trim($data)) == "description"){ $validHeaders++; $fieldMapping['description'] = $entry;}
								}
								
								//Process CSV Contents if valid Headers are found
								if($validHeaders == 4){
									$entryCount = 0;
									$filtereditems = 0;
									$invalidItems = 0;
									$invalidCharacters = 0;
									$invalidLines = '';
									$lineCount = 0;
									$invalidLineCount = 0;
									
									foreach($fileContentsArray as $entry => $data){
										if($entry != 0){
											$lineCount++;
											//Filter the content of the entry of the CSV
											//NOTE: Quotation Marks (") are currently illegal
											$tempEntrydata = filter_var(trim($data),FILTER_VALIDATE_REGEXP, Array('options'=> Array('regexp' => "/^(?:([a-z]|[A-Z]|[0-9]|-|:|,|'|@|\.|;|\/|\(|\\|\&|#|\*|\s){1,})$/")));
											
											if($tempEntrydata != ""){
												$temp = explode(",",$tempEntrydata);
												
												if(count($temp) == 4){		
													$tempMacAddress = filter_var(trim($temp[$fieldMapping['macaddress']]),FILTER_VALIDATE_REGEXP, Array('options'=> Array('regexp' => '/^(?:[A-F]|[a-f]|[0-9]){2}(\:|-){1}(?:[A-F]|[a-f]|[0-9]){2}(\:|-){1}(?:[A-F]|[a-f]|[0-9]){2}(\:|-){1}(?:[A-F]|[a-f]|[0-9]){2}(\:|-){1}(?:[A-F]|[a-f]|[0-9]){2}(\:|-){1}(?:[A-F]|[a-f]|[0-9]){2}$/')));
													$tempFullname = filter_var(trim($temp[$fieldMapping['fullname']]),FILTER_SANITIZE_STRING);
													$tempEmail = filter_var(trim($temp[$fieldMapping['emailaddress']]),FILTER_SANITIZE_EMAIL);
													
													if($tempMacAddress != "" && $tempFullname != "" && $tempEmail != ""){
														$csvFile[$entryCount]['macAddress'] = $tempMacAddress;
														$csvFile[$entryCount]['fullname'] = $tempFullname;
														$csvFile[$entryCount]['emailaddress'] = $tempEmail;
														$csvFile[$entryCount]['description'] = filter_var(trim($temp[$fieldMapping['description']]),FILTER_SANITIZE_STRING);
														$entryCount++;
													}else{
														$filtereditems++;
														$invalidLineCount++;
														$invalidLines = $invalidLines.($invalidLineCount > 1 ? ',' : '' ).' '.$lineCount;
													}
												}else{
													$invalidItems++;
													$invalidLineCount++;
													$invalidLines = $invalidLines.($invalidLineCount > 1 ? ',' : '' ).' '.$lineCount;
												}
											}else{
												$invalidCharacters++;
												$invalidLineCount++;
												$invalidLines = $invalidLines.($invalidLineCount > 1 ? ',' : '' ).' '.$lineCount;
											}	
										}
									}
									
									$csvFile['count'] = $entryCount;
									
									$uploadData = dechex(time());
									
									$_SESSION['bulk-import'][$uploadData] = $csvFile;
									
									$returnResult['result'] = true;
									$returnResult['validitems'] = $entryCount;
									$returnResult['filtereditems'] = $filtereditems;
									$returnResult['invaliditems'] = $invalidItems;
									$returnResult['invalidchar'] = $invalidCharacters;
									$returnResult['uploadkey'] = $uploadData;
									
									if($returnResult['validitems'] == $returnResult['recordsprocessed']){
										$returnResult['message'] = "Successful Upload";
									}else{
										$returnResult['message'] = "Successful Upload with exceptions on lines ".$invalidLines;
									}			
								}else{
									
									$returnResult['result'] = false;
									$returnResult['message'] = "Invalid CSV Header Mapping";
									$returnResult['validitems'] = 0;
									$returnResult['filtereditems'] = 0;
									$returnResult['invaliditems'] = 0;
									$returnResult['invalidchar'] = 0;
								}
							}else{
								$returnResult['result'] = false;
								$returnResult['message'] = "Invalid CSV Header Column Count";
								$returnResult['validitems'] = 0;
								$returnResult['filtereditems'] = 0;
								$returnResult['invaliditems'] = 0;
								$returnResult['invalidchar'] = 0;
							}
						}else{
							$returnResult['result'] = false;
							$returnResult['message'] = "Invalid CSV Header";
							$returnResult['validitems'] = 0;
							$returnResult['filtereditems'] = 0;
							$returnResult['invaliditems'] = 0;
							$returnResult['invalidchar'] = 0;
						}
					}else{
						$returnResult['result'] = false;
						$returnResult['message'] = "Invalid CSV Header Attributes";
						$returnResult['validitems'] = 0;
						$returnResult['filtereditems'] = 0;
						$returnResult['invaliditems'] = 0;
						$returnResult['invalidchar'] = 0;
					}	
				}else{
					$returnResult['result'] = false;
					$returnResult['message'] = "CSV has no contents";
					$returnResult['validitems'] = 0;
					$returnResult['filtereditems'] = 0;
					$returnResult['invaliditems'] = 0;
					$returnResult['invalidchar'] = 0;
				}
			}else{
				$returnResult['result'] = false;
				$returnResult['message'] = "Invalid CSV File Contents";
				$returnResult['validitems'] = 0;
				$returnResult['filtereditems'] = 0;
				$returnResult['invaliditems'] = 0;
				$returnResult['invalidchar'] = 0;
			}
		}else{
			$returnResult['result'] = false;
			$returnResult['message'] = "File contains invalid characters";
			$returnResult['validitems'] = 0;
			$returnResult['filtereditems'] = 0;
			$returnResult['invaliditems'] = 0;
			$returnResult['invalidchar'] = 0;
		}			
	}else{
		$returnResult['result'] = false;
		$returnResult['message'] = "Invalid File Extension";
		$returnResult['validitems'] = 0;
		$returnResult['filtereditems'] = 0;
		$returnResult['invaliditems'] = 0;
		$returnResult['invalidchar'] = 0;
	}


	print json_encode($returnResult);
?>