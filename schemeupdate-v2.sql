/**
 *
 *Copyright (c) 2020 Cisco and/or its affiliates.
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


/* INSTALLATION README -----------------------------------------------------------
 *	Replace the following values below with your specific installation information
 *  Refer to 'DONOTDELETE-iPSKManager-Install.txt' for your environment details
 *
 *  A total of three(3) entries need updating in this SQL file:
 *
 *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
 *			Example: USE `iPSKManager`;
 *			
 *		<ISE_DB_USERNAME> MySQL Username for Cisco ISE ODBC Connection
 *			Example: CREATE DEFINER=`ciscoise`@`%` PROC...
 *--------------------------------------------------------------------------------
 */

SET AUTOCOMMIT = 0;

/* UPDATE: Replace <ISE_DB_NAME> with the Database Name created when installed*/
USE `<ISE_DB_NAME>`;

START TRANSACTION;

--
-- Drop Existing Procedures
--

DROP PROCEDURE `iPSK_MACLookup`;
DROP PROCEDURE `iPSK_MACLookupNonExpired`;

DELIMITER $$
--
-- Updated Procedures
--

/* UPDATE: Replace <ISE_DB_USERNAME> with the Database Username created when installing the Database*/

CREATE DEFINER=`<ISE_DB_USERNAME>`@`%` PROCEDURE `iPSK_MACLookup` (IN `username` VARCHAR(64))  SQL SECURITY INVOKER
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
END$$


/* UPDATE: Replace <ISE_DB_USERNAME> with the Database Username created when installing the Database*/

CREATE DEFINER=`<ISE_DB_USERNAME>`@`%` PROCEDURE `iPSK_MACLookupNonExpired` (IN `username` VARCHAR(64))  SQL SECURITY INVOKER
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
END$$

DELIMITER ;

-- --------------------------------------------------------
--
-- Update Identity PSK Manager Database Scheme Version
--
UPDATE `settings` SET `value` = '2' WHERE `page` = 'global' AND `settingClass` = 'db-schema' AND `keyName` = 'version';

COMMIT;
