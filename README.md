# Identity PSK Manager for Cisco ISE

*Identity Pre-Shared Key(PSK) Manager that simplifies the management and provisioning of unique PSK to devices within your environment.  This is a standalone application which integrates with Cisco ISE through an ODBC Connection and Cisco ISE's APIs.*

---

## Overview

Identity PSK ("IPSK") Manager for Cisco ISE provides a way to manage the full Life Cycle of Pre Shared Keys for supported Hardware/Software through Cisco ISE.  

The sample code provided is designed to integrate with Cisco ISE via ODBC, External RESTful Services (ERS) API, and Monitoring API.  

In addition, both the 'Sponsor' & 'Captive' Portals can be customized.  You can edit the existing templates or create new ones that can be added to the manager.

The ODBC Connection provides the core functionality between IPSK Manager and Cisco ISE, while the API's are for other minor functionality such as performing Change of Authorizations ("CoA") and creation of Authorization Profiles for IPSK Captive Portals.

## Features

Identity PSK Manager enables the following features/functionality:

- ODBC Integration with Cisco ISE as an External Identity Source
- Cisco ISE ERS API Integration
- Cisco ISE Monitoring API Integration
- Internal iPSK Identity Store for Management of Administration & Portal Access
- LDAP & Active Directory Authenication Capable
- Customizable Authorization Profiles (Unique or Random PSK on a per Device or User basis)
- Customizable Endpoint Groups
- Customizable Portal Groups
- Customizable Sponsor & Captive Portals

## Technologies & Frameworks Used

**Cisco Products:**

- Cisco ISE v2.4 (or greater)

**Tools & Frameworks:**

- Bootstrap v4.3.1
- jQuery v3.5.0
- feathericon
- Chart JS v3.3.0
- ClipBoard Copy v2.0.4

**We would like to thank all the authors & contributers on the previously mentioned Tools & Frameworks!**

The Documentation and Guides available from these tools, contained the details needed to bring this Application to life.  Below are links to references used for the above Tools & Frameworks:
- Bootstrap - Mobile first website Framework [https://getbootstrap.com/docs/5.0/getting-started/introduction/](https://getbootstrap.com/docs/5.0/getting-started/introduction/)
- jQuery - Feature-rich JavaScript Library [https://api.jquery.com/](https://api.jquery.com/)
- feathericon - Simply beautiful open sources icons - [https://feathericons.com/](https://feathericons.com/)
- Chart JS - Simple and Flexible Javascript Charts - [https://www.chartjs.org/docs/latest/](https://www.chartjs.org/docs/latest/)
- ClipBoard Copy - Javascript based copy to clipboard - [https://clipboardjs.com/](https://clipboardjs.com/)

## Prerequisites

- Cisco ISE v2.4 (or greater)
- Apache Web Server
- PHP 7.2 or greater
  - Required Modules: **mbstring, ldap, mysqli, mysqlnd, curl, simplexml, xml, sodium, and json**
- MySQL or MariaDB

## Installation

#### Ubuntu 18.04.x LTS

1. After installing Ubuntu OS, make sure the system is up-to-date:
```
admin@ubuntu:~$ sudo apt-get update
admin@ubuntu:~$ sudo apt-get upgrade
```
2. After updating Ubuntu OS, install Apache2, PHP7, MySQL server, and additional modules:
```
admin@ubuntu:~$ sudo apt-get install php apache2 mysql-server php-mysqlnd php-ldap php-curl php-mbstring php-xml
```
3. Enable Apache Modules:
```
admin@ubuntu:~$ sudo a2enmod rewrite
admin@ubuntu:~$ sudo a2enmod ssl
```
4. Download iPSK Manager from GitHub
```
admin@ubuntu:~$ sudo git clone https://github.com/CiscoSE/iPSK-Manager.git /var/www/iPSK-Manager
[sudo] password for admin: 
Cloning into '/var/www/iPSK-Manager'...
remote: Enumerating objects: 13, done.
remote: Counting objects: 100% (13/13), done.
remote: Compressing objects: 100% (13/13), done.
remote: Total 261 (delta 6), reused 0 (delta 0), pack-reused 248
Receiving objects: 100% (261/261), 311.44 KiB | 2.29 MiB/s, done.
Resolving deltas: 100% (141/141), done.
admin@ubuntu:~$ 
```
5. (Recommended) Run post installation script for MySQL
```
admin@ubuntu:~$ sudo mysql_secure_installation utility
```
Note: For more information on the MySQL or MariaDB secure installation utility, please review: 
- [MySQL Secure Installation](https://dev.mysql.com/doc/refman/5.7/en/mysql-secure-installation.html)
- [MariaDB Secure Installation](https://mariadb.com/kb/en/mysql_secure_installation)

6. (Recommended) Instead of using MySQL root account, a temporary 'install' account can be created to install the iPSK Manager then removed once completed
```
admin@ubuntu:~$ sudo mysql -p
Enter password: 
Welcome to the MySQL monitor. Commands end with ; or \g.
Your MySQL connection id is 1080
Server version: 5.7.27-0ubuntu0.18.04.1 (Ubuntu)

Copyright (c) 2000, 2019, Oracle and/or its affiliates. All rights reserved.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql> CREATE USER 'install'@'%' IDENTIFIED BY '{SOME PASSWORD}'
mysql> GRANT ALL PRIVILEGES ON *.* TO 'install'@'%' WITH GRANT OPTION;
mysql> FLUSH PRIVILEGES;
mysql> exit
```
7. Change owner of the iPSK-Manager directory (Showing example of Ubuntu distribution which uses www-data user and group for the apache process)
```
admin@ubuntu:~$ cd /var/www
admin@ubuntu:~$ sudo chown www-data:www-data -R iPSK-Manager
```
8. It is recommended to use SSL for security and subsequent section describes how to enable SSL. However, if no certificate is available,follow the instructions in the Appendix on how to use non-SSL port for the portals

9. (Recommended) Create self-signed certificate using OpenSSL or external tools. You will need private key, signed certificate, and CA chain if applicable

10. (Recommended) Enable SSL for admin portal. There are sample apache configuration files for the admin portal and end user portal located at the root of the install directory called 'portal-ssl.sample.conf' file. There are 3 sections in the file for admin portal and also for enabling port 8443 & 8445 for SSL. You can simply copy each section in to separate files and place them in '/etc/apache2/sites-enabled' to get it enabled. Aside from that you need to make sure to update the path and file names for the certificate. First for admin portal create a file called '443-ssl.conf' with following content: 
```
<IfModule mod_ssl.c>
<VirtualHost *:443>
ServerAdmin webmaster@ipskmanager

DocumentRoot /var/www/iPSK-Manager/adminportal

<Directory /var/www/iPSK-Manager/adminportal>
AllowOverride All
</Directory>

ErrorLog ${APACHE_LOG_DIR}/admin-error.log
CustomLog ${APACHE_LOG_DIR}/admin-access.log combined

# SSL Engine Switch:
# Enable/Disable SSL for this virtual host.
SSLEngine on

# A self-signed (snakeoil) certificate can be created by installing
# the ssl-cert package. See
# /usr/share/doc/apache2/README.Debian.gz for more info.
# If both key and certificate are stored in the same file, only the
# SSLCertificateFile directive is needed.
SSLCertificateFile /path/to/my/ssl.crt
SSLCertificateKeyFile /path/to/my/ssl.key

# Server Certificate Chain:
# Point SSLCertificateChainFile at a file containing the
# concatenation of PEM encoded CA certificates which form the
# certificate chain for the server certificate. Alternatively
# the referenced file can be the same as SSLCertificateFile
# when the CA certificates are directly appended to the server
# certificate for convinience.
SSLCertificateChainFile /path/to/my/ssl.chain

<FilesMatch "\.(cgi|shtml|phtml|php)$">
SSLOptions +StdEnvVars
</FilesMatch>

</VirtualHost>
</IfModule>
```
Note: Make sure to modify the path and file name for the certificate, private key, and the certificate chain

11. (Recommended) Enable SSL for end user portal port. Next for end user portal create a file called '8443-ssl.conf' with following content:
```
<IfModule mod_ssl.c>

Listen 8443

<VirtualHost *:8443>

ServerAdmin webmaster@ipskmanager

DocumentRoot /var/www/iPSK-Manager/portals

<Directory /var/www/iPSK-Manager/portals>
AllowOverride All
</Directory>

ErrorLog ${APACHE_LOG_DIR}/portal-8443-error.log
CustomLog ${APACHE_LOG_DIR}/portal-8443-access.log combined

# SSL Engine Switch:
# Enable/Disable SSL for this virtual host.
SSLEngine on

# A self-signed (snakeoil) certificate can be created by installing
# the ssl-cert package. See
# /usr/share/doc/apache2/README.Debian.gz for more info.
# If both key and certificate are stored in the same file, only the
# SSLCertificateFile directive is needed.
SSLCertificateFile /path/to/my/ssl.crt
SSLCertificateKeyFile /path/to/my/ssl.key

# Server Certificate Chain:
# Point SSLCertificateChainFile at a file containing the
# concatenation of PEM encoded CA certificates which form the
# certificate chain for the server certificate. Alternatively
# the referenced file can be the same as SSLCertificateFile
# when the CA certificates are directly appended to the server
# certificate for convinience.
SSLCertificateChainFile /path/to/my/ssl.chain

<FilesMatch "\.(cgi|shtml|phtml|php)$">
SSLOptions +StdEnvVars
</FilesMatch>

</VirtualHost>
</IfModule>
```

12. (Recommended) Once SSL is enabled restart apache. This time you will be asked to enter password to access the private key file: 
```
admin@ubuntu:~$ sudo service apache2 restart
Enter passphrase for SSL/TLS keys for 127.0.1.1:443 (RSA): *********
admin@ubuntu:~$ 
```
13. Run setup via browser. Open web browser from any machine and go to the IP or hostname (If DNS is already setup) of the IPSK Manager host: https://portal.authc.net or https://192.168.201.90/

14. You will be greeted with setup screen, click Next and accept the license agreement page and click Next to continue with setup

15. Installer will also make sure that required PHP modules are installed, if any of the modules are missing go back to the CLI and make sure they are installed and rerun the Installer

16. Accept default values or change values as needed

| Field Name  | 	Sample Entry  |	 Note |
|-------------|-----------------|-------|
|mySQL Server IP/FQDN|	127.0.0.1	 |     |
|iPSK Database Username	| ipsk-db-user  | A random password will be generated at the end of installation process|
|Cisco ISE ODBC Username	| ipsk-ise-user  |	This is the username ISE will use for SQL connection. A random password will be generated at the end of installation process
|iPSK Database Name	| ipsk	|
|MySQL Admin/Root Username	| install	| If using temporary MySQL install account, if not use root account
|MySQL Admin/Root Password	| ******** | If using temporary MySQL install account, if not use root password
 

17. You will also be asked to create local GUI administrator account password

18. If the install fails, please make sure to go through the steps above to see any of the steps were missed

19. At the end of setup process, it will automatically download a txt file called **'DONOTDELETE-iPSKMANAGER-Install.txt'** which contains the database details including username & password needed for ISE communication such as following:
```
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
## iPSK Manager
## DO NOT DELETE THIS DATA - STORE IN A SECURE LOCATION
## THIS FILE CONTAINS DETAILS ABOUT YOUR INSTALLATION
########################################################

#Organization SID for iPSK Manager
#---------------------------------
Organization (System) SID Value = S-1-9-1569991369-1569991369-1

#Encryption Key for Encrypting MySQL Sensitive Data
#--------------------------------------------------
Encryption Key = AipsBSIhIJ+TnwsYkLlw1fTPSXc/siDQoP8YaTWZNpY=

#iPSKManager Database Credentials
#--------------------------------
Host = 127.0.0.1
Username = ipsk-db-user
Password = t@DKrkNyZhvXnUTd
Database = ipsk

#Cisco ISE MySQL Credentials
#---------------------------
Username = ipsk-ise-user
Password = e1YV3JefcDQut8g
Database = ipsk

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
```
Note: Keep this file safe in case iPSK Manager needs to be restored or new ISE / iPSK Manager integration is needed

20. You should be redirected to the iPSK Manager login page where you can enter the credential (default GUI admin username is "**administrator**") created during the setup to login to proceed with iPSK Manager configuration

21. Allow SQL connection from other hosts, by editing the '**/etc/mysql/mysql.conf.d/mysqld.cnf**' file. Find the line '**bind-address = 127.0.0.1**' and add '**#**' at the front to remark it

Note: Please make sure to utilize MySQL security best practices such as FW rules and limiting mySQL user to specific hosts as above allows SQL access from all hosts

22. Restart MySQL service by running "**sudo service mysql restart**"

23. (Optional) If temporary MySQL account was created in previous step, run the following to remove the 'install' account
```
admin@ubuntu:~$ sudo mysql -p
Enter password: 
Welcome to the MySQL monitor. Commands end with ; or \g.
Your MySQL connection id is 1080
Server version: 5.7.27-0ubuntu0.18.04.1 (Ubuntu)

Copyright (c) 2000, 2019, Oracle and/or its affiliates. All rights reserved.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql> REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'install'@'%';
mysql> FLUSH PRIVILEGES;
mysql> DROP USER 'install'@'%';
```

## Appendix
## (Experimental) Keeping iPSK Manager up to date
When there is an update to the Git repository, local iPSK Manager deployment can be updated without reinstallation

1. Make sure to make backups of the install directory and the database, and also the config.php file should be backed up
```
admin@ubuntu:~$ sudo cp /var/www/iPSK-Manager/supportfiles/include/config.php /some/backup/directory/
```
2. Go to iPSK Manager install directory
```
admin@ubuntu:~$ cd /var/www/iPSK-Manager
```
3. Pull repository
```
admin@ubuntu:~$ sudo git pull
```
## Notes about Ubuntu 20.04 LTS based installation
Perform the following steps after the IPSK Manager setup:

Update the MySQL Configuration located in ‘/etc/mysql/mysql.conf.d/mysqld.cnf’ and add the following line below.

**default_authentication_plugin=mysql_native_password**

Then restart the MySQL Service or Reboot the system.
```
admin@ubuntu:~$ sudo service mysql restart
```
Then update the ISE MySQL credential with mysql_native_password to make it compatibe with ISE
```
admin@ubuntu:~$ sudo mysql -p
mysql> ALTER USER 'ipsk-ise-user'@'%' IDENTIFIED WITH mysql_native_password BY '{PASSWORD}';
mysql> FLUSH PRIVILEGES;
```

## (Experimental) GUI Logging
Logging via GUI can be enabled by editing the **'additionalmenus.json'** file in **/var/www/iPSK-Manager/supportfiles/adminportals/modules/** directory. Change the "menuEnabled" flag at the end to 1 (default is 0) as shown below and refresh admin GUI and you will see 'System Logging' option visible just below 'About' settings. Note that logging view currently lacks few features to make it useable beyond basic troubleshooting.
```
{"0":{"id":"menuLogging","module":"logging","data-feather":"flag","menuText":"System Logging"},"menuItems":1,"menuEnabled":1}
```
Note: Rest of the logging settings are under Platform Configuration > Advanced Settings and Logging Settings
## Use non-SSL port for admin and end user portal
It is recommended to use SSL for security and main section of the document describes how to enable SSL. However, if no certificate is available, port 80 request to admin portal can be used by creating a file called '80.conf' with following content and placed in '/etc/apache2/sites-enabled' directory: 
```
<VirtualHost *:80>
# The ServerName directive sets the request scheme, hostname and port that
# the server uses to identify itself. This is used when creating
# redirection URLs. In the context of virtual hosts, the ServerName
# specifies what hostname must appear in the request's Host: header to
# match this virtual host. For the default virtual host (this file) this
# value is not decisive as it is used as a last resort host regardless.
# However, you must set it for any further virtual host explicitly.
#ServerName www.example.com

ServerAdmin webmaster@localhost
DocumentRoot /var/www/iPSK-Manager/adminportal

<Directory /var/www/iPSK-Manager/adminportal>
AllowOverride All
</Directory>

# Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
# error, crit, alert, emerg.
# It is also possible to configure the loglevel for particular
# modules, e.g.
#LogLevel info ssl:warn

ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined

# For most configuration files from conf-available/, which are
# enabled or disabled at a global level, it is possible to
# include a line for only one particular virtual host. For example the
# following line enables the CGI configuration for this host only
# after it has been globally disabled with "a2disconf".
#Include conf-available/serve-cgi-bin.conf

</VirtualHost>
```

Note: May need to remove default config file in the '/etc/apache2/sites-enabled' directory

Next, point port 8080 request to end user portal by creating a file called '8080.conf' with following content and place it in '/etc/apache2/sites-enabled' directory: 

```
Listen 8080

<VirtualHost *:8080>
# The ServerName directive sets the request scheme, hostname and port that
# the server uses to identify itself. This is used when creating
# redirection URLs. In the context of virtual hosts, the ServerName
# specifies what hostname must appear in the request's Host: header to
# match this virtual host. For the default virtual host (this file) this
# value is not decisive as it is used as a last resort host regardless.
# However, you must set it for any further virtual host explicitly.
#ServerName www.example.com

ServerAdmin webmaster@localhost
DocumentRoot /var/www/iPSK-Manager/portals

<Directory /var/www/iPSK-Manager/portals>
AllowOverride All
</Directory>

# Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
# error, crit, alert, emerg.
# It is also possible to configure the loglevel for particular
# modules, e.g.
#LogLevel info ssl:warn

ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined

# For most configuration files from conf-available/, which are
# enabled or disabled at a global level, it is possible to
# include a line for only one particular virtual host. For example the
# following line enables the CGI configuration for this host only
# after it has been globally disabled with "a2disconf".
#Include conf-available/serve-cgi-bin.conf

</VirtualHost>
```

Note: Within iPSK manager admin portal, go to Portals and make sure the end user portals are configured with port 8080
 
Lastly, restart apache service: 
```
admin@ubuntu:~$ sudo service apache2 restart
```
## Authors

- Gary Oppel
- Hosuk Won

## License

This project is licensed to you under the terms of the [Apache License, Version 2.0](./LICENSE).

---

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
