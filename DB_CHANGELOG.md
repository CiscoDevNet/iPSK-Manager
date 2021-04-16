# Identity PSK Manager - Database Change Log

Current Database Schema Version : **3**

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
> mysql -u root -p < schemeupdate-v2.sql
> ```
OR
> ```
> mysql -u <USER> -p < schemeupdate-v2.sql
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
> mysql -u root -p < schemeupdate-v3.sql
> ```
OR
> ```
> mysql -u <USER> -p < schemeupdate-v3.sql
> ```
5) Enter password when prompted
