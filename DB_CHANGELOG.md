# Identity PSK Manager - Database Change Log

Current Database Schema Version : **6**

All changes to this Sample Codes Database will be documented here:

------

Initial Release (10/02/2019) - v1
------

* Initial Sample Code Commit

Database Changes (11/02/2020) - v2
------

### Fixed
- Updates the to following Stored Procedures to send approriate ODBC results to Cisco ISE
  - iPSK_MACLookup
  - iPSK_MACLookupNonExpired

**WARNING: This update affects the stored procedures used by Cisco ISE's ODBC Connection to Query for Endpoints**

Updates to the stored procedures require running the included `schemaupdate-v2.sql` update script against the database.
1) Download and update the `schemaupdate-v2.sql` file with your environment specific variables as per README
> ```
> /* INSTALLATION README -----------------------------------------------------------
> *  Replace the following values below with your specific installation information
> *  Refer to 'DONOTDELETE-iPSKManager-Install.txt' for you environment details
> *
> * A total of three(3) entries need updating in this SQL file:
> *
> *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
> *			Example: USE `iPSKManager`;
> * 			
> *		<ISE_DB_USERNAME> MySQL Username for Cisco ISE ODBC Connection
> *			Example: CREATE DEFINER=`ciscoise`@`%` PROC...
> *--------------------------------------------------------------------------------
> */
> ```
2) Login to the CLI of the Server running MySQL
3) Change to the directory where the script is located
4) Execute the script with 'root' or a user with 'CREATE / DROP STORED PROCEDURE' Privileges
> ```
> mysql -u root -p < schemaupdate-v2.sql
> ```
OR
> ```
> mysql -u <USER> -p < schemaupdate-v2.sql
> ```
5) Enter password when prompted


Database Changes (04/15/2021) - v3
------

### Fixed
- Updates the to following Stored Procedures to send approriate ODBC results to Cisco ISE
  - iPSK_AuthMACPlain
  - iPSK_AuthMACPlainNonExpired

**WARNING: This update affects the stored procedures used by Cisco ISE's ODBC Connection to Query for Endpoints**

Updates to the stored procedures require running the included `schemaupdate-v3.sql` update script against the database.
1) Download and update the `schemaupdate-v3.sql` file with your environment specific variables as per README
> ```
> /* INSTALLATION README -----------------------------------------------------------
> *  Replace the following values below with your specific installation information
> *  Refer to 'DONOTDELETE-iPSKManager-Install.txt' for you environment details
> *
> * A total of three(3) entries need updating in this SQL file:
> *
> *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
> *			Example: USE `iPSKManager`;
> * 			
> *		<ISE_DB_USERNAME> MySQL Username for Cisco ISE ODBC Connection
> *			Example: CREATE DEFINER=`ciscoise`@`%` PROC...
> *--------------------------------------------------------------------------------
> */
> ```
2) Login to the CLI of the Server running MySQL
3) Change to the directory where the script is located
4) Execute the script with 'root' or a user with 'CREATE / DROP STORED PROCEDURE' Privileges
> ```
> mysql -u root -p < schemaupdate-v3.sql
> ```
OR
> ```
> mysql -u <USER> -p < schemaupdate-v3.sql
> ```
5) Enter password when prompted

Database Changes (06/14/2024) - v4
------

### Changed
- Added column to LDAP table to support directories other then Active Directory
- Added column to endpoint table for last updated time to support future functionality

Updates to the data tables require running the included `schemaupdate-v4.sql` update script against the database.
1) Download and update the `schemaupdate-v4.sql` file with your environment specific variables as per README
> ```
> /* INSTALLATION README -----------------------------------------------------------
> *  Replace the following values below with your specific installation information
> *  Refer to 'DONOTDELETE-iPSKManager-Install.txt' for you environment details
> *
> * A total of one(1) entry needs updating in this SQL file:
> *
> *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
> *			Example: USE `iPSKManager`;
> * 			
> *--------------------------------------------------------------------------------
> */
> ```
2) Login to the CLI of the Server running MySQL
3) Change to the directory where `schemaupdate-v4.sql` is located
4) Execute the script with 'root' or a user with 'ALTER TABLE' Privileges
> ```
> mysql -u root -p < schemaupdate-v4.sql
> ```
OR
> ```
> mysql -u <USER> -p < schemaupdate-v4.sql
> ```
5) Enter password when prompted

Database Changes (11/29/2024) - v5
------

### Changed
- Added columns to endpoints table to support VLAN and dACL assignment
- Added columns to authorizationTemplates table to support VLAN and dACL assignment
- Update the to following Stored Procedure to send VLAN and dACL assignments
  - iPSK_AttributeFetch

**WARNING: This update affects the stored procedures used by Cisco ISE's ODBC Connection to Query for Endpoints**

Updates to the data tables require running the included `schemaupdate-v5.sql` update script against the database.
1) Download and update the `schemaupdate-v5.sql` file with your environment specific variables as per README
> ```
> /* INSTALLATION README -----------------------------------------------------------
> *  Replace the following values below with your specific installation information
> *  Refer to 'DONOTDELETE-iPSKManager-Install.txt' for you environment details
> *
> * A total of two(2) entries need updating in this SQL file:
> *
> *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
> *			Example: USE `iPSKManager`;
> * 			
> *		<ISE_DB_USERNAME> MySQL Username for Cisco ISE ODBC Connection
> *			Example: CREATE DEFINER=`ciscoise`@`%` PROC...
> *--------------------------------------------------------------------------------
> */
> ```
2) Login to the CLI of the Server running MySQL
3) Change to the directory where `schemaupdate-v5.sql` is located
4) Execute the script with 'root' or a user with 'ALTER TABLE' Privileges
> ```
> mysql -u root -p < schemaupdate-v5.sql
> ```
OR
> ```
> mysql -u <USER> -p < schemaupdate-v5.sql
> ```
5) Enter password when prompted

Database Changes (5/16/2025) - v6
------

### Changed
- Reapplication of schema changes in v5 for issue with fresh installs and missing db items due to missing create functions in installation code

**WARNING: This update affects the stored procedures used by Cisco ISE's ODBC Connection to Query for Endpoints**

Updates to the data tables require running the included `schemaupdate-v6.sql` update script against the database.
1) Download and update the `schemaupdate-v6.sql` file with your environment specific variables as per README
> ```
> /* INSTALLATION README -----------------------------------------------------------
> *  Replace the following values below with your specific installation information
> *  Refer to 'DONOTDELETE-iPSKManager-Install.txt' for you environment details
> *
> * A total of two(2) entries need updating in this SQL file:
> *
> *		<ISE_DB_NAME> = MySQL iPSK Manager Database Name
> *			Example: USE `iPSKManager`;
> * 			
> *		<ISE_DB_USERNAME> MySQL Username for Cisco ISE ODBC Connection
> *			Example: CREATE DEFINER=`ciscoise`@`%` PROC...
> *--------------------------------------------------------------------------------
> */
> ```
2) Login to the CLI of the Server running MySQL
3) Change to the directory where `schemaupdate-v6.sql` is located
4) Execute the script with 'root' or a user with 'ALTER TABLE' Privileges
> ```
> mysql -u root -p < schemaupdate-v6.sql
> ```
OR
> ```
> mysql -u <USER> -p < schemaupdate-v6.sql
> ```
5) Enter password when prompted