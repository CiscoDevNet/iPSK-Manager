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


## Key Design Requirements
- Add something here

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

## Application Usage

Please visit [http://cs.co/iPSK-Manager](http://cs.co/iPSK-Manager)

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
