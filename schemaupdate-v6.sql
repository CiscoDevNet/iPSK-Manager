/**
 *
 *
 *Copyright 2025 Cisco Systems, Inc. or its affiliates
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
 *  A total of three(3) entries needs updating in this SQL file:
 *
 *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
 *			Example: USE `iPSKManager`;
 *
 *		<IPSK_DB_USERNAME> =  MySQL Username for iPSK Manager
 *			Example: CREATE DEFINER=`ipskmgr`@`%` PROC...
 *
 *		<ISE_DB_USERNAME> =  MySQL Username for Cisco ISE ODBC Connection
 *			Example: CREATE DEFINER=`ciscoise`@`%` PROC...
 *			
 *--------------------------------------------------------------------------------
 */

SET AUTOCOMMIT = 0;

/* UPDATE: Replace <ISE_DB_NAME> with the Database Name created when installed*/

USE `<ISE_DB_NAME>`;

START TRANSACTION;

--
-- Drop Existing Trigger
--

DROP TRIGGER IF EXISTS `lastupdate_before_update_trigger`;

--
-- Create Trigger
--

DELIMITER $$
CREATE DEFINER=`<IPSK_DB_USERNAME>`@`%` TRIGGER `lastupdate_before_update_trigger` BEFORE UPDATE ON `endpoints` FOR EACH ROW BEGIN
    SET NEW.lastUpdated = NOW();
END
$$
DELIMITER ;

--
-- Drop Existing Procedure
--

DROP PROCEDURE IF EXISTS `iPSK_AttributeFetch`;

--
-- Updated Procedure
--

/* UPDATE: Replace <ISE_DB_USERNAME> with the Database Username created when installing the Database*/

DELIMITER $$
CREATE DEFINER=`<ISE_DB_USERNAME>`@`%` PROCEDURE `iPSK_AttributeFetch` (IN `username` VARCHAR(64), OUT `result` INT)  SQL SECURITY INVOKER
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
		SELECT 'Empty' AS fullName, 'Empty' AS emailAddress, 'Empty' AS createdBy, 'Empty' AS description, '0' AS expirationDate, 'False' AS accountExpired, 'EMPTY' AS pskValue, 'EMPTY' as pskValuePlain, 'Empty' AS vlan, 'Empty' AS dacl;
	ELSE
	  IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC) THEN
		SET result=0;
		SELECT fullName,emailAddress,createdBy,description,expirationDate,accountExpired,pskValue, RIGHT(pskValue, LENGTH(pskValue) - 4) as pskValuePlain,vlan,dacl FROM endpoints WHERE endpoints.macAddress = @formattedMAC;
	  ELSE
		SET result=1;
	  END IF;
	END CASE;
END
$$
DELIMITER ;

COMMIT;