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


/**
 *@author	Gary Oppel (gaoppel@cisco.com)
 *@author	Hosuk Won (howon@cisco.com)
 *@contributor	Drew Betz (anbetz@cisco.com)
 */
	
	ini_set('display_errors', 'Off');
	
	//Installer Configuration Output
	$configurationFile = <<< 'DATA'
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
	
	
	//Displaying of Errors Global setting
	error_reporting(E_ALL);
	ini_set('display_errors', 'Off');
	ini_set('display_startup_errors', 'Off');
	
	//SET GLOBAL VARIABLES
	$globalSmtpEnabled = false;
	$globalDateOutputFormat = 'm/d/Y @ g:i A';
	
	//ORGANIZATION SID VARIABLES


DATA;

	$configurationFile .= "\t$"."baseSid = 'S-1-9';\n";
	$configurationFile .= "\t$"."orgSid = '$orgSid';\n";
	$configurationFile .= "\t$"."systemSid = '1';\n\n\n";
	$configurationFile .= "\t$"."encryptionKey = '$encryptionKey';\n\n";
	$configurationFile .= "\t$"."dbHostname = '{$_SESSION['dbhostname']}';\n";
	$configurationFile .= "\t$"."dbUsername = '{$_SESSION['dbusername']}';\n";
	$configurationFile .= "\t$"."dbPassword = '$managerDbPassword';\n";
	$configurationFile .= "\t$"."dbDatabase = '{$_SESSION['databasename']}';\n";
	$configurationFile .= "\n?>";
	
	//Installation Configuration Backup Output
	$installDetails = <<< TEXT
#Copyright 2021 Cisco Systems, Inc. or its affiliates
#
#Licensed under the Apache License, Version 2.0 (the "License");
#you may not use this file except in compliance with the License.
#You may obtain a copy of the License at
#
#  http://www.apache.org/licenses/LICENSE-2.0
#
#Unless required by applicable law or agreed to in writing, software
#distributed under the License is distributed on an "AS IS" BASIS,
#WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#See the License for the specific language governing permissions and
#limitations under the License.

########################################################
##				iPSK Manager
##  DO NOT DELETE THIS DATA - STORE IN A SECURE LOCATION
##  THIS FILE CONTAINS DETAILS ABOUT YOUR INSTALLATION
########################################################

#Organization SID for iPSK Manager
#---------------------------------
Organization (System) SID Value = $baseSid-$orgSid-1

#Encryption Key for Encrypting MySQL Sensitive Data
#--------------------------------------------------
Encryption Key = $encryptionKey

#iPSKManager Database Credentials
#--------------------------------
Host = {$_SESSION['dbhostname']}
Username = {$_SESSION['dbusername']}
Password = $managerDbPassword
Database = {$_SESSION['databasename']}

#Cisco ISE MySQL Credentials
#---------------------------
Username = {$_SESSION['iseusername']}
Password = $iseDbPassword
Database = {$_SESSION['databasename']}

#Cisco ISE Stored Procedures Names
#---------------------------------
iPSK_AttributeFetch
iPSK_AuthMACPlain
iPSK_FetchGroups
iPSK_FetchPasswordForMAC
iPSK_MACLookup

###OPTIONAL### Cisco ISE Replacement Stored Procedures for returning only Non-Expired Endpoints Contained within the iPSK Database
#---------------------------------------------------------------------------------------------------------------------------------
iPSK_AuthMACPlainNonExpired
iPSK_FetchPasswordForMACNonExpired
iPSK_MACLookupNonExpired

TEXT;
	
	//Installer SQL Build for Tables, Initial Data, Table Alterations, and 
	$sqlProcedure[0] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_AttributeFetch` (IN `username` VARCHAR(64), OUT `result` INT)  SQL SECURITY INVOKER
BEGIN
	IF username = '*' THEN
		SELECT username INTO @formattedMAC;
	ELSE
		SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
		SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;
	END IF;
	
	CASE @formattedMAC
	WHEN '*' THEN
		SET result=0;
		SELECT 'Empty' AS fullName, 'Empty' AS emailAddress, 'Empty' AS createdBy, 'Empty' AS description, '0' AS expirationDate, 'False' AS accountExpired, 'EMPTY' AS pskValue, 'EMPTY' as pskValuePlain, 'Empty' AS vlan, 'Empty' AS dacl;;
	ELSE
	  IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC) THEN
		SET result=0;
		SELECT fullName,emailAddress,createdBy,description,expirationDate,accountExpired,pskValue, RIGHT(pskValue, LENGTH(pskValue) - 4) as pskValuePlain,vlan,dacl FROM endpoints WHERE endpoints.macAddress = @formattedMAC;
	  ELSE
		SET result=1;
	  END IF;
	END CASE;
END
SQL;

	$sqlProcedure[1] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_AuthMACPlain` (IN `username` VARCHAR(64), IN `password` VARCHAR(255))  SQL SECURITY INVOKER
BEGIN
	SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
	SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;

	IF NOT (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 0 THEN
		IF NOT (SELECT accountExpired FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 'True' THEN
			IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) < UNIX_TIMESTAMP(NOW()) THEN
				UPDATE `endpoints` SET `endpoints`.`accountExpired` = 'True' WHERE `endpoints`.`macAddress` = @formattedMAC;
			END IF;
		END IF;
	END IF;
	
	IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND endpoints.password = password ) THEN
		SELECT 0,11,'This is a very good user, give him all access','no error';
	ELSE
		SELECT 3, 0, 'odbc','ODBC Authen Error';
	END IF;
END
SQL;

	$sqlProcedure[2] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_AuthMACPlainNonExpired` (IN `username` VARCHAR(64), IN `password` VARCHAR(255))  SQL SECURITY INVOKER
BEGIN
	SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
	SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;

	IF NOT (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 0 THEN
		IF NOT (SELECT accountExpired FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 'True' THEN
			IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) < UNIX_TIMESTAMP(NOW()) THEN
				UPDATE `endpoints` SET `endpoints`.`accountExpired` = 'True' WHERE `endpoints`.`macAddress` = @formattedMAC;
			END IF;
		END IF;
	END IF;
	
	IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND endpoints.password = password ) THEN
		IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1) = 0 THEN
			SELECT 0,11,'This is a very good user, give him all access','no error';
		ELSE
			IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1 AND expirationDate > UNIX_TIMESTAMP(NOW())) THEN
				SELECT 0,11,'This is a very good user, give him all access','no error';
			ELSE
				SELECT 10001, 0, 'Account Disabled','ODBC Authen Error';
			END IF;
		END IF;
	ELSE
		IF EXISTS(SELECT * FROM `unknownEndpoints` WHERE `unknownEndpoints`.`macAddress` = @formattedMAC) THEN
			UPDATE `unknownEndpoints` SET `unknownEndpoints`.`lastSeen` = CURRENT_TIMESTAMP WHERE `unknownEndpoints`.`macAddress` = @formattedMAC;
		ELSE
			INSERT INTO `unknownEndpoints` (`macAddress`,`createdBy`) VALUES(@formattedMAC ,'SYSTEM-ODBC');
		END IF;
		
		SELECT 3, 0, 'odbc','ODBC Authen Error';
	END IF;
END
SQL;

	$sqlProcedure[3] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_FetchGroups` (IN `username` VARCHAR(64), OUT `result` INT)  SQL SECURITY INVOKER
BEGIN
	IF username = '*' THEN
		SELECT username INTO @formattedMAC;
	ELSE
		SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
		SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;
	END IF;

	CASE @formattedMAC
	WHEN '*' THEN
		SELECT DISTINCT groupName AS groupname FROM endpointGroups;
	ELSE
		SELECT endpointGroups.groupName FROM endpointAssociations
		INNER JOIN endpoints ON endpoints.id = endpointAssociations.endpointId
		INNER JOIN endpointGroups ON endpointGroups.id = endpointAssociations.epGroupId
		where endpoints.macAddress = @formattedMAC;
	END CASE;
	
	SET result = 0;
END
SQL;

	$sqlProcedure[4] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_FetchPasswordForMAC` (IN `username` VARCHAR(64))  SQL SECURITY INVOKER
BEGIN
	SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
		
	SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;
	
	IF NOT (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 0 THEN
		IF NOT (SELECT accountExpired FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 'True' THEN
			IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) < UNIX_TIMESTAMP(NOW()) THEN
				UPDATE `endpoints` SET `endpoints`.`accountExpired` = 'True' WHERE `endpoints`.`macAddress` = @formattedMAC;
			END IF;
		END IF;
	END IF;
	
	IF EXISTS (SELECT * from endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1) THEN
		SELECT 0,11,'This is a very good user, give him all access','no error',password FROM endpoints WHERE endpoints.macAddress = @formattedMAC;
	ELSE
		SELECT 3, 0, 'odbc','ODBC Authen Error';
	END IF;
END
SQL;

	$sqlProcedure[5] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_FetchPasswordForMACNonExpired` (IN `username` VARCHAR(64))  SQL SECURITY INVOKER
BEGIN
	SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
		
	SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;
	
	IF NOT (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 0 THEN
		IF NOT (SELECT accountExpired FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 'True' THEN
			IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) < UNIX_TIMESTAMP(NOW()) THEN
				UPDATE `endpoints` SET `endpoints`.`accountExpired` = 'True' WHERE `endpoints`.`macAddress` = @formattedMAC;
			END IF;
		END IF;
	END IF;
	
	IF EXISTS (SELECT * from endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1) THEN
		IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1) = 0 THEN
			SELECT 0,11,'This is a very good user, give him all access','no error',password FROM endpoints WHERE endpoints.macAddress = @formattedMAC;
		ELSE
			IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1 AND expirationDate > UNIX_TIMESTAMP(NOW())) THEN
				SELECT 0,11,'This is a very good user, give him all access','no error',password FROM endpoints WHERE endpoints.macAddress = @formattedMAC;
			ELSE
				SELECT 10001, 0, 'Account Disabled','ODBC Authen Error';
			END IF;
		END IF;		
	ELSE
		SELECT 3, 0, 'odbc','ODBC Authen Error';
	END IF;
END
SQL;

	$sqlProcedure[6] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_MACLookup` (IN `username` VARCHAR(64))  SQL SECURITY INVOKER
BEGIN
	SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
	SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;
	
	IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC) THEN
		IF NOT (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 0 THEN
			IF NOT (SELECT accountExpired FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 'True' THEN
				IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) < UNIX_TIMESTAMP(NOW()) THEN
					UPDATE `endpoints` SET `endpoints`.`accountExpired` = 'True' WHERE `endpoints`.`macAddress` = @formattedMAC;
				END IF;
			END IF;
		END IF;
	END IF;
	
	IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC) THEN
		UPDATE `endpoints` SET `endpoints`.`lastAccessed` = CURRENT_TIMESTAMP WHERE `endpoints`.`macAddress` = @formattedMAC;
		
		IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1) THEN
			SELECT 0,11,'This is a very good user, give him all access','no error';
		ELSE
			IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 0) THEN
				SELECT 10001, 0, 'Account Disabled','ODBC Authen Error';
			ELSE
				SELECT 4, 0, 'odbc','ODBC Authen Error';
			END IF;
		END IF;
	ELSE
		IF EXISTS(SELECT * FROM `unknownEndpoints` WHERE `unknownEndpoints`.`macAddress` = @formattedMAC) THEN
			UPDATE `unknownEndpoints` SET `unknownEndpoints`.`lastSeen` = CURRENT_TIMESTAMP WHERE `unknownEndpoints`.`macAddress` = @formattedMAC;
		ELSE
			INSERT INTO `unknownEndpoints` (`macAddress`,`createdBy`) VALUES(@formattedMAC ,'SYSTEM-ODBC');
		END IF;
		
		SELECT 1, 0, 'odbc','ODBC Authen Error';
		
	END IF;
END
SQL;

	$sqlProcedure[7] = <<< SQL
CREATE DEFINER=`{$_SESSION['iseusername']}`@`%` PROCEDURE `iPSK_MACLookupNonExpired` (IN `username` VARCHAR(64))  SQL SECURITY INVOKER
BEGIN
	SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
	SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;
	
	IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC) THEN
		IF NOT (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 0 THEN
			IF NOT (SELECT accountExpired FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 'True' THEN
				IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) < UNIX_TIMESTAMP(NOW()) THEN
					UPDATE `endpoints` SET `endpoints`.`accountExpired` = 'True' WHERE `endpoints`.`macAddress` = @formattedMAC;
				END IF;
			END IF;
		END IF;
	END IF;
	
	IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC) THEN
		UPDATE `endpoints` SET `endpoints`.`lastAccessed` = CURRENT_TIMESTAMP WHERE `endpoints`.`macAddress` = @formattedMAC;
		
		IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1 AND expirationDate = 0) THEN
			SELECT 0,11,'This is a very good user, give him all access','no error';
		ELSE
			IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 1 AND expirationDate > UNIX_TIMESTAMP(NOW())) THEN
				SELECT 0,11,'This is a very good user, give him all access','no error';
			ELSE
				IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND accountEnabled = 0) THEN
					SELECT 10001, 0, 'Account Disabled','ODBC Authen Error';
				ELSE
					IF EXISTS(SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND expirationDate < UNIX_TIMESTAMP(NOW())) THEN
						SELECT 10002, 0, 'Account Expired','ODBC Authen Error';
					ELSE
						SELECT 4, 0, 'odbc','ODBC Authen Error';
					END IF;
				END IF;
			END IF;
		END IF;
	ELSE
		IF EXISTS(SELECT * FROM `unknownEndpoints` WHERE `unknownEndpoints`.`macAddress` = @formattedMAC) THEN
			UPDATE `unknownEndpoints` SET `unknownEndpoints`.`lastSeen` = CURRENT_TIMESTAMP WHERE `unknownEndpoints`.`macAddress` = @formattedMAC;
		ELSE
			INSERT INTO `unknownEndpoints` (`macAddress`,`createdBy`) VALUES(@formattedMAC ,'SYSTEM-ODBC');
		END IF;
		
		SELECT 1, 0, 'odbc','ODBC Authen Error';
		
	END IF;
END
SQL;

	$sqlTrigger[0] = <<< SQL
CREATE TRIGGER `lastupdate_before_update_trigger` BEFORE UPDATE ON `endpoints` FOR EACH ROW BEGIN
    SET NEW.lastUpdated = NOW();
END
SQL;


	$sqlTable[0] = "CREATE TABLE `authorizationTemplates` (`id` int(11) NOT NULL, `authzPolicyName` varchar(255) NOT NULL, `authzPolicyDescription` varchar(255) NOT NULL, `ciscoAVPairPSKMode` varchar(10) NOT NULL DEFAULT 'ascii',  `ciscoAVPairPSK` varchar(68) NOT NULL, `pskLength` int(11) NOT NULL DEFAULT '8', `termLengthSeconds` int(11) NOT NULL, `vlan` VARCHAR(255) NULL DEFAULT NULL, `dacl` VARCHAR(255) NULL DEFAULT NULL, `visible` tinyint(1) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[1] = "CREATE TABLE `endpointAssociations` (`id` int(11) NOT NULL, `endpointId` int(11) NOT NULL, `macAddress` varchar(17) NOT NULL, `epGroupId` int(11) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[2] = "CREATE TABLE `endpointGroups` (`id` int(11) NOT NULL, `groupName` varchar(25) NOT NULL, `description` varchar(255) NOT NULL, `enabled` tinyint(1) NOT NULL DEFAULT '1', `authzTemplateId` int(11) NOT NULL, `visible` tinyint(1) NOT NULL DEFAULT '1', `notificationPermission` int(11) NOT NULL DEFAULT '0', `parentSite` int(11) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[3] = "CREATE TABLE `endpoints` (`id` int(11) NOT NULL, `macAddress` varchar(17) NOT NULL, `password` varchar(255) NOT NULL, `expirationDate` int(11) NOT NULL, `accountExpired` varchar(5) NOT NULL DEFAULT 'False', `accountEnabled` tinyint(1) NOT NULL DEFAULT '1', `fullName` varchar(255) NOT NULL, `description` varchar(255) NOT NULL, `emailAddress` varchar(255) NOT NULL, `pskValue` varchar(68) NOT NULL, `vlan` varchar(255) NULL DEFAULT NULL, `dacl` varchar(255) NULL DEFAULT NULL, `lastAccessed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[4] = "CREATE TABLE `internalGroups` (`id` int(11) NOT NULL, `groupName` varchar(64) NOT NULL, `groupType` int(11) NOT NULL DEFAULT '0', `description` varchar(255) NOT NULL, `groupDn` varchar(255) NOT NULL, `permissions` bigint(20) NOT NULL, `visible` tinyint(1) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[5] = "CREATE TABLE `internalUserGroupMapping` (`id` bigint(11) NOT NULL, `userId` int(11) NOT NULL, `groupId` int(11) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[6] = "CREATE TABLE `internalUsers` (`id` int(11) NOT NULL, `userName` varchar(25) NOT NULL, `password` varchar(255) NOT NULL, `fullName` varchar(255) NOT NULL, `description` varchar(255) NOT NULL DEFAULT '', `email` varchar(255) NOT NULL DEFAULT '', `dn` varchar(255) NOT NULL DEFAULT '', `sid` varchar(255) NOT NULL DEFAULT '', `enabled` tinyint(1) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[7] = "CREATE TABLE `ldapServers` (`id` int(11) NOT NULL, `adConnectionName` varchar(255) NOT NULL, `adServer` varchar(255) NOT NULL, `adDomain` varchar(255) NOT NULL, `adUsername` varchar(255) NOT NULL, `adPassword` varchar(255) NOT NULL, `adBaseDN` varchar(255) NOT NULL, `adSecure` int(11) NOT NULL, `directoryType` INT(11) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[8] = "CREATE TABLE `portalHostnames` (`id` int(11) NOT NULL, `hostname` varchar(255) NOT NULL, `visible` int(11) NOT NULL DEFAULT '1', `enabled` int(11) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[9] = "CREATE TABLE `portalPorts` (`id` int(11) NOT NULL, `portalPort` int(11) NOT NULL, `portalSecure` tinyint(1) NOT NULL DEFAULT '0', `enabled` tinyint(1) NOT NULL DEFAULT '1', `visible` tinyint(1) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[10] = "CREATE TABLE `settings` (`id` int(11) NOT NULL, `page` varchar(255) NOT NULL, `settingClass` varchar(25) NOT NULL, `keyName` varchar(255) NOT NULL, `optionIndex` int(11) NOT NULL DEFAULT '0', `value` varchar(2048) NOT NULL, `encrypted` tinyint(1) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$sqlTable[11] = "CREATE TABLE `sites` (`id` int(11) NOT NULL, `siteName` varchar(25) NOT NULL, `siteLocation` varchar(255) NOT NULL, `siteOwner` varchar(25) NOT NULL, `parent` int(11) NOT NULL, `visible` tinyint(1) NOT NULL DEFAULT '1', `enabled` tinyint(1) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[12] = "CREATE TABLE `sponsorGroupEPGMapping` (`id` int(11) NOT NULL, `sponsorGroupId` int(11) NOT NULL, `endpointGroupId` int(11) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[13] = "CREATE TABLE `sponsorGroupInternalMapping` (`id` int(11) NOT NULL, `sponsorGroupId` int(11) NOT NULL, `internalGroupId` int(11) NOT NULL, `groupPermissions` int(11) NOT NULL DEFAULT '0', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[14] = "CREATE TABLE `sponsorGroupPortalMapping` (`id` int(11) NOT NULL, `sponsorGroupId` int(11) NOT NULL, `sponsorPortalId` int(11) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[15] = "CREATE TABLE `sponsorGroups` (`id` int(11) NOT NULL, `sponsorGroupName` varchar(255) NOT NULL, `sponsorGroupDescription` varchar(255) NOT NULL, `sponsorGroupType` int(11) NOT NULL DEFAULT '0', `sponsorGroupAuthType` int(11) NOT NULL DEFAULT '0', `members` longtext NOT NULL, `maxDevices` int(11) NOT NULL DEFAULT '5', `permissions` int(11) NOT NULL DEFAULT '0', `visible` tinyint(1) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[16] = "CREATE TABLE `sponsorGroupSSIDMapping` (`id` int(11) NOT NULL, `sponsorGroupId` int(11) NOT NULL, `wirelessSSIDId` int(11) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[17] = "CREATE TABLE `sponsorPortals` (`id` int(11) NOT NULL, `portalName` varchar(32) NOT NULL, `description` varchar(255) NOT NULL, `portalId` varchar(64) NOT NULL, `portalType` int(11) NOT NULL DEFAULT '0', `enabled` tinyint(1) NOT NULL, `visible` tinyint(1) NOT NULL DEFAULT '1', `portalHostname` varchar(255) NOT NULL, `portalTcpPort` int(11) NOT NULL DEFAULT '0', `portalSecure` tinyint(1) NOT NULL DEFAULT '0', `enforceHostname` tinyint(1) NOT NULL DEFAULT '0', `enforceTcpPort` tinyint(1) NOT NULL DEFAULT '0', `enforceSecure` tinyint(1) NOT NULL DEFAULT '0', `portalTemplate` varchar(255) NOT NULL DEFAULT 'default', `authenticationDirectory` varchar(7) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[18] = "CREATE TABLE `sponsorPortalTypes` (`id` int(11) NOT NULL, `portalTypeName` varchar(32) NOT NULL, `portalTypeDescription` varchar(255) NOT NULL, `maxSponsorGroups` int(11) NOT NULL DEFAULT '1', `maxEndpointsOverride` tinyint(1) NOT NULL DEFAULT '0', `maxEndpointsAllowed` int(11) NOT NULL DEFAULT '5', `portalClass` varchar(15) NOT NULL, `portalModule` varchar(25) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[19] = "CREATE TABLE `unknownEndpoints` (`id` int(11) NOT NULL, `macAddress` varchar(17) NOT NULL, `createdBy` varchar(255) NOT NULL, `lastSeen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[20] = "CREATE TABLE `userSidCache` (`id` int(11) NOT NULL, `sid` varchar(255) NOT NULL, `userPrincipalName` varchar(255) NOT NULL, `samAccountName` varchar(255) NOT NULL, `userDn` varchar(255) NOT NULL, `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[21] = "CREATE TABLE `wirelessNetworks` (`id` int(11) NOT NULL, `ssidName` varchar(32) NOT NULL, `ssidDescription` varchar(255) NOT NULL, `ssidSiteId` int(11) NOT NULL DEFAULT '1', `enabled` tinyint(1) NOT NULL DEFAULT '1', `visible` tinyint(1) NOT NULL DEFAULT '1', `createdBy` varchar(255) NOT NULL, `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$sqlTable[22] = "CREATE TABLE `logging` ( `id` bigint(20) NOT NULL, `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `sessionID` varchar(255) NOT NULL, `fileName` varchar(255) NOT NULL, `functionName` varchar(255) NOT NULL, `className` varchar(255) NOT NULL, `classMethodName` varchar(255) NOT NULL, `lineNumber` int(11) NOT NULL, `message` longtext NOT NULL, `logDataPayload` longtext NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1";

	$sqlAlterTable[0] = "ALTER TABLE `authorizationTemplates` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[1] = "ALTER TABLE `endpointAssociations` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `macAddress` (`macAddress`), ADD KEY `epGroupId` (`epGroupId`), ADD KEY `endpointId` (`endpointId`)";
	$sqlAlterTable[2] = "ALTER TABLE `endpointGroups` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[3] = "ALTER TABLE `endpoints` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `macAddress` (`macAddress`)";
	$sqlAlterTable[4] = "ALTER TABLE `internalGroups` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `groupName` (`groupName`)";
	$sqlAlterTable[5] = "ALTER TABLE `internalUserGroupMapping` ADD PRIMARY KEY (`id`), ADD KEY `internalUserGroupMapping_ibfk_1` (`userId`), ADD KEY `groupId` (`groupId`)";
	$sqlAlterTable[6] = "ALTER TABLE `internalUsers` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[7] = "ALTER TABLE `ldapServers` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[8] = "ALTER TABLE `portalHostnames` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[9] = "ALTER TABLE `portalPorts` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `portalPort` (`portalPort`)";
	$sqlAlterTable[10] = "ALTER TABLE `settings` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[11] = "ALTER TABLE `sites` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[12] = "ALTER TABLE `logging` ADD PRIMARY KEY (`id`);";
	$sqlAlterTable[13] = "ALTER TABLE `sponsorGroupEPGMapping` ADD PRIMARY KEY (`id`), ADD KEY `portalId` (`sponsorGroupId`), ADD KEY `endpointGroupId` (`endpointGroupId`)";
	$sqlAlterTable[14] = "ALTER TABLE `sponsorGroupInternalMapping` ADD PRIMARY KEY (`id`), ADD KEY `portalId` (`sponsorGroupId`), ADD KEY `endpointGroupId` (`internalGroupId`)";
	$sqlAlterTable[15] = "ALTER TABLE `sponsorGroupPortalMapping` ADD PRIMARY KEY (`id`), ADD KEY `portalId` (`sponsorGroupId`), ADD KEY `endpointGroupId` (`sponsorPortalId`)";
	$sqlAlterTable[16] = "ALTER TABLE `sponsorGroups` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[17] = "ALTER TABLE `sponsorGroupSSIDMapping` ADD PRIMARY KEY (`id`), ADD KEY `portalId` (`sponsorGroupId`), ADD KEY `endpointGroupId` (`wirelessSSIDId`)";
	$sqlAlterTable[18] = "ALTER TABLE `sponsorPortals` ADD PRIMARY KEY (`id`), ADD KEY `portalType` (`portalType`)";
	$sqlAlterTable[19] = "ALTER TABLE `sponsorPortalTypes` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[20] = "ALTER TABLE `unknownEndpoints` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `macAddress` (`macAddress`)";
	$sqlAlterTable[21] = "ALTER TABLE `userSidCache` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `sid` (`sid`)";
	$sqlAlterTable[22] = "ALTER TABLE `wirelessNetworks` ADD PRIMARY KEY (`id`)";
	$sqlAlterTable[23] = "ALTER TABLE `authorizationTemplates` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[24] = "ALTER TABLE `endpointAssociations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[25] = "ALTER TABLE `endpointGroups` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[26] = "ALTER TABLE `endpoints` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[27] = "ALTER TABLE `internalGroups` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[28] = "ALTER TABLE `internalUserGroupMapping` MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[29] = "ALTER TABLE `internalUsers` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[30] = "ALTER TABLE `ldapServers` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[31] = "ALTER TABLE `portalHostnames` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[32] = "ALTER TABLE `portalPorts` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[33] = "ALTER TABLE `settings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[34] = "ALTER TABLE `sites` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[35] = "ALTER TABLE `sponsorGroupEPGMapping` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[36] = "ALTER TABLE `sponsorGroupInternalMapping` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[37] = "ALTER TABLE `sponsorGroupPortalMapping` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[38] = "ALTER TABLE `sponsorGroups` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[39] = "ALTER TABLE `sponsorGroupSSIDMapping` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[40] = "ALTER TABLE `sponsorPortals` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[41] = "ALTER TABLE `sponsorPortalTypes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[42] = "ALTER TABLE `unknownEndpoints` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[43] = "ALTER TABLE `userSidCache` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[44] = "ALTER TABLE `wirelessNetworks` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT";
	$sqlAlterTable[45] = "ALTER TABLE `logging` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT";

	//iPSK Manager - Table Contraints
	$sqlConstraint[0] = "ALTER TABLE `endpointAssociations` ADD CONSTRAINT `endpointAssociations_ibfk_2` FOREIGN KEY (`epGroupId`) REFERENCES `endpointGroups` (`id`), ADD CONSTRAINT `endpointAssociations_ibfk_3` FOREIGN KEY (`endpointId`) REFERENCES `endpoints` (`id`)";
	$sqlConstraint[1] = "ALTER TABLE `internalUserGroupMapping` ADD CONSTRAINT `internalUserGroupMapping_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `internalUsers` (`id`), ADD CONSTRAINT `internalUserGroupMapping_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `internalGroups` (`id`)";
	$sqlConstraint[2] = "ALTER TABLE `sponsorGroupEPGMapping` ADD CONSTRAINT `sponsorGroupEPGMapping_ibfk_1` FOREIGN KEY (`sponsorGroupId`) REFERENCES `sponsorGroups` (`id`), ADD CONSTRAINT `sponsorGroupEPGMapping_ibfk_2` FOREIGN KEY (`endpointGroupId`) REFERENCES `endpointGroups` (`id`)";
	$sqlConstraint[3] = "ALTER TABLE `sponsorGroupInternalMapping` ADD CONSTRAINT `sponsorGroupInternalMapping_ibfk_1` FOREIGN KEY (`internalGroupId`) REFERENCES `internalGroups` (`id`), ADD CONSTRAINT `sponsorGroupInternalMapping_ibfk_2` FOREIGN KEY (`sponsorGroupId`) REFERENCES `sponsorGroups` (`id`)";
	$sqlConstraint[4] = "ALTER TABLE `sponsorGroupPortalMapping` ADD CONSTRAINT `sponsorGroupPortalMapping_ibfk_1` FOREIGN KEY (`sponsorPortalId`) REFERENCES `sponsorPortals` (`id`), ADD CONSTRAINT `sponsorGroupPortalMapping_ibfk_2` FOREIGN KEY (`sponsorGroupId`) REFERENCES `sponsorGroups` (`id`)";
	$sqlConstraint[5] = "ALTER TABLE `sponsorGroupSSIDMapping` ADD CONSTRAINT `sponsorGroupSSIDMapping_ibfk_1` FOREIGN KEY (`sponsorGroupId`) REFERENCES `sponsorGroups` (`id`), ADD CONSTRAINT `sponsorGroupSSIDMapping_ibfk_2` FOREIGN KEY (`wirelessSSIDId`) REFERENCES `wirelessNetworks` (`id`)";
	$sqlConstraint[6] = "ALTER TABLE `sponsorPortals` ADD CONSTRAINT `sponsorPortals_ibfk_1` FOREIGN KEY (`portalType`) REFERENCES `sponsorPortalTypes` (`id`)";
	
	$menuConfig = "{\"0\":{\"id\":\"menuDashboard\",\"module\":\"dashboard\",\"data-feather\":\"home\",\"menuText\":\"Dashboard\"},\"1\":{\"id\":\"manageEndpoints\",\"module\":\"endpoints\",\"data-feather\":\"list\",\"menuText\":\"Managed iPSK EndPoints\"},\"2\":{\"id\":\"menuPortals\",\"module\":\"portals\",\"data-feather\":\"grid\",\"menuText\":\"Portals\"},\"3\":{\"id\":\"menuSponsorGroups\",\"module\":\"sponsorgroups\",\"data-feather\":\"users\",\"menuText\":\"Portal Groups\"},\"4\":{\"id\":\"menuEpGrouping\",\"module\":\"epgroup\",\"data-feather\":\"monitor\",\"menuText\":\"Endpoint Grouping\"},\"5\":{\"id\":\"menuAuthorizationPolicies\",\"module\":\"authz\",\"data-feather\":\"lock\",\"menuText\":\"Authorization Templates\"},\"6\":{\"id\":\"menuWirelessNetworks\",\"module\":\"wireless\",\"data-feather\":\"wifi\",\"menuText\":\"Wireless Networks\"},\"7\":{\"id\":\"menuInternalUsers\",\"module\":\"internalusers\",\"data-feather\":\"user\",\"menuText\":\"Internal Identities - Users\"},\"8\":{\"id\":\"menuInternalGroups\",\"module\":\"internalgroups\",\"data-feather\":\"users\",\"menuText\":\"Groups\"},\"9\":{\"id\":\"menuLdap\",\"module\":\"ldap\",\"data-feather\":\"server\",\"menuText\":\"LDAP Servers\"},\"10\":{\"id\":\"menuConfig\",\"module\":\"sysconfig\",\"data-feather\":\"settings\",\"menuText\":\"Platform Configuration\"},\"11\":{\"id\":\"menuAbout\",\"module\":\"about\",\"data-feather\":\"info\",\"menuText\":\"About\"},\"menuItems\":12}";
	
	//iPSK Manager - Populate Database
	$sqlInsert[0] = "INSERT INTO `internalGroups` (`id`, `groupName`, `groupType`, `description`, `groupDn`, `permissions`, `visible`, `createdBy`, `createdDate`) VALUES(1, 'Administrators', 0, 'Internal Administrators Group', 'CN=Administrators,CN=Groups,DC=System,DC=Local', 1, 1, '$systemSID', '2019-05-01 00:00:00')";
	$sqlInsert[1] = "INSERT INTO `internalUsers` (`id`, `userName`, `password`, `fullName`, `description`, `email`, `dn`, `sid`, `enabled`, `createdBy`, `createdDate`) VALUES(1, 'Administrator', '$ipskManagerAdminPassword', 'Built-In Administrator', 'Built-in System Administrator Account', '', 'CN=Administrator,CN=Users,DC=System,DC=Local', '$adminSID', 1, '$systemSID', '2019-05-01 00:00:00')";
	$sqlInsert[2] = "INSERT INTO `internalUserGroupMapping` (`id`, `userId`, `groupId`, `createdBy`, `createdDate`) VALUES(1, 1, 1, '$systemSID', '2019-05-01 00:00:00')";
	$sqlInsert[3] = "INSERT INTO `portalPorts` (`id`, `portalPort`, `portalSecure`, `enabled`, `visible`, `createdBy`, `createdDate`) VALUES(1, 80, 0, 1, 1, '$systemSID', '2019-05-01 00:00:00'),(2, 8080, 0, 1, 1, '$systemSID', '2019-05-01 00:00:00'),(3, 443, 1, 1, 1, '$systemSID', '2019-05-01 00:00:00'),(4, 8443, 1, 1, 1, '$systemSID', '2019-05-01 00:00:00'),(5, 8444, 1, 1, 1, '$systemSID', '2019-05-01 00:00:00'),(6, 8445, 1, 1, 1, '$systemSID', '2019-05-01 00:00:00')";
	$sqlInsert[4] = "INSERT INTO `settings` (`id`, `page`, `settingClass`, `keyName`, `optionIndex`, `value`, `encrypted`) VALUES (1, 'global', 'platform-config', 'system-configured', 0, '1', 0), (2, 'global', 'platform-config', 'logging-level', 0, '3', 0),(3, 'global', 'db-schema', 'version', 0, '6', 0),(4, 'global', 'admin-portal', 'admin-portal-hostname', 0, '', 0),(5, 'global', 'admin-portal', 'admin-portal-strict-hostname', 0, '', 0),(6, 'global', 'admin-portal', 'redirect-on-hostname-match', 0, '', 0),(7, 'global', 'menu-config', 'adminMenu', 0, '$menuConfig', 0),(8, 'global', 'ise-ers-credentials', 'enabled', 0, '', 0),(9, 'global', 'ise-ers-credentials', 'ersHost', 0, '', 0),(10, 'global', 'ise-ers-credentials', 'ersUsername', 0, '', 0),(11, 'global', 'ise-ers-credentials', 'ersPassword', 0, '', 1),(12, 'global', 'ise-ers-credentials', 'verify-ssl-peer', 0, '', 0),(13, 'global', 'ise-mnt-credentials', 'enabled', 0, '', 0),(14, 'global', 'ise-mnt-credentials', 'mntHost', 0, '', 0),(15, 'global', 'ise-mnt-credentials', 'mntUsername', 0, '', 0),(16, 'global', 'ise-mnt-credentials', 'mntPassword', 0, '', 1),(17, 'global', 'ise-mnt-credentials', 'verify-ssl-peer', 0, '', 0),(18, 'global', 'smtp-settings', 'smtp-hostname', 0, '', 0),(19, 'global', 'smtp-settings', 'smtp-port', 0, '', 0),(20, 'global', 'smtp-settings', 'smtp-username', 0, '', 0),(21, 'global', 'smtp-settings', 'smtp-password', 0, '', 1),(22, 'global', 'smtp-settings', 'smtp-fromaddress', 0, '', 0),(23, 'global', 'smtp-settings', 'enabled', 0, '', 0),(24, 'global', 'smtp-settings', 'smtp-encryption', 0, '', 0)";
	$sqlInsert[5] = "INSERT INTO `sites` (`id`, `siteName`, `siteLocation`, `siteOwner`, `parent`, `visible`, `enabled`, `createdBy`, `createdDate`) VALUES(1, 'Global', 'Global', 'System', 1, 1, 1, '$systemSID', '2019-05-01 00:00:00')";
	$sqlInsert[6] = "INSERT INTO `userSidCache` (`id`, `sid`, `userPrincipalName`, `samAccountName`, `userDn`, `createdBy`, `createdDate`) VALUES(1, '$systemSID', 'SYSTEM', 'SYSTEM', 'DC=System,DC=Local','$systemSID', '2019-05-01 00:00:00'),(2, '$adminSID', 'Administrator@System.Local', 'Administrator', 'CN=Administrator,CN=Users,DC=System,DC=Local', '$systemSID', '2019-05-01 00:00:00')";
	$sqlInsert[7] = "INSERT INTO `sponsorPortalTypes` (`id`, `portalTypeName`, `portalTypeDescription`, `maxSponsorGroups`, `maxEndpointsOverride`, `maxEndpointsAllowed`, `portalClass`, `portalModule`) VALUES(1, 'Sponsor Portal', 'Portal which allows Sponsors the ability to enroll devices into the iPSK system for access.', 1, 0, 5, 'core', 'sponsorportal'),(2, 'Captive Portal', 'Captive Portal allowing Users to login to enroll their device into the iPSK system.', 3, 0, 5, 'core', 'captiveportal')";
	
	$managerSqlUser[0] = "CREATE USER '{$_SESSION['dbusername']}'@'%' IDENTIFIED BY '$managerDbPassword'";

	$managerSqlPermissions[0] = "GRANT USAGE ON *.* TO '{$_SESSION['dbusername']}'@'%'";
	$managerSqlPermissions[1] = "GRANT ALL PRIVILEGES ON `{$_SESSION['databasename']}`.* TO '{$_SESSION['dbusername']}'@'%' WITH GRANT OPTION";

	$iseSqlUser[0] = "CREATE USER '{$_SESSION['iseusername']}'@'%' IDENTIFIED BY '$iseDbPassword'";
	
	$iseSqlPermissions[0] = "GRANT USAGE ON *.* TO '{$_SESSION['iseusername']}'@'%'";
	$iseSqlPermissions[1] = "GRANT SELECT, EXECUTE ON `{$_SESSION['databasename']}`.* TO '{$_SESSION['iseusername']}'@'%'";
	//$iseSqlPermissions[3] = "GRANT SELECT ON `mysql`.`proc` TO '{$_SESSION['iseusername']}'@'%'";
	$iseSqlPermissions[2] = "GRANT UPDATE (lastAccessed, accountExpired) ON `{$_SESSION['databasename']}`.`endpoints` TO '{$_SESSION['iseusername']}'@'%'";
	$iseSqlPermissions[3] = "GRANT INSERT (lastSeen, createdBy, macAddress), UPDATE (lastSeen) ON `{$_SESSION['databasename']}`.`unknownEndpoints` TO '{$_SESSION['iseusername']}'@'%'";
	
?>
