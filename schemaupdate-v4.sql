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
 *  A total of one(1) entry needs updating in this SQL file:
 *
 *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
 *			Example: USE `iPSKManager`;
 *			
 *--------------------------------------------------------------------------------
 */

SET AUTOCOMMIT = 0;

/* UPDATE: Replace <ISE_DB_NAME> with the Database Name created when installed*/
USE `<ISE_DB_NAME>`;

START TRANSACTION;

DELIMITER ;

-- --------------------------------------------------------
--
-- Update Identity PSK Manager Database Endpoints Table
--
ALTER TABLE `endpoints` ADD `lastUpdated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `createdDate`;

-- --------------------------------------------------------
--
-- Update Identity PSK Manager Database LDAP Table
--
ALTER TABLE `ldapServers` ADD `directoryType` INT(11) NOT NULL AFTER `adSecure`;

-- --------------------------------------------------------
--
-- Update Identity PSK Manager Database Scheme Version
--
UPDATE `settings` SET `value` = '4' WHERE `page` = 'global' AND `settingClass` = 'db-schema' AND `keyName` = 'version';

COMMIT;