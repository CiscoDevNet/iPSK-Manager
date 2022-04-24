/**
 *
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

DROP PROCEDURE `iPSK_AuthMACPlain`;
DROP PROCEDURE `iPSK_AuthMACPlainNonExpired`;

DELIMITER $$
--
-- Updated Procedures
--

/* UPDATE: Replace <ISE_DB_USERNAME> with the Database Username created when installing the Database*/

CREATE DEFINER=`<ISE_DB_USERNAME>`@`%` PROCEDURE `iPSK_AuthMACPlain` (IN `username` VARCHAR(64), IN `password` VARCHAR(255))  SQL SECURITY INVOKER
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
END$$

/* UPDATE: Replace <ISE_DB_USERNAME> with the Database Username created when installing the Database*/

CREATE DEFINER=`<ISE_DB_USERNAME>`@`%` PROCEDURE `iPSK_AuthMACPlainNonExpired` (IN `username` VARCHAR(64), IN `password` VARCHAR(255))  SQL SECURITY INVOKER
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
END$$

DELIMITER ;

-- --------------------------------------------------------
--
-- Update Identity PSK Manager Database Scheme Version
--
UPDATE `settings` SET `value` = '3' WHERE `page` = 'global' AND `settingClass` = 'db-schema' AND `keyName` = 'version';

COMMIT;
