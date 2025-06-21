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

	if($sanitizedInput['module-action'] == "general"){
		$ipskISEDB->setGlobalSetting("admin-portal","admin-portal-hostname", $sanitizedInput['adminPortalHostname']);
		$ipskISEDB->setGlobalSetting("admin-portal","admin-portal-strict-hostname", $sanitizedInput['strict-hostname']);
		$ipskISEDB->setGlobalSetting("admin-portal","redirect-on-hostname-match", $sanitizedInput['redirect-hostname']);
		$ipskISEDB->setGlobalSetting("admin-portal","log-purge-interval", $sanitizedInput['logPurgeInterval']);
		$ipskISEDB->setGlobalSetting("admin-portal","use-portal-description", $sanitizedInput['usePortalDescription']);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "ldap"){
		$ipskISEDB->setGlobalSetting("ldap-settings","ldap-ssl-check", $sanitizedInput['ldapSSLCheck']);
		$ipskISEDB->setGlobalSetting("ldap-settings","nested-groups", $sanitizedInput['nestedGroups']);

		print true;
	}elseif($sanitizedInput['module-action'] == "saml"){
		$ipskISEDB->setGlobalSetting("saml-settings","enabled", $sanitizedInput['samlEnabled']);
		$ipskISEDB->setGlobalSetting("saml-settings","ldap-source", $sanitizedInput['samlLdapSource']);
		$ipskISEDB->setGlobalSetting("saml-settings","headers", $sanitizedInput['samlHeaders']);
		$ipskISEDB->setGlobalSetting("saml-settings","usernamefield", $sanitizedInput['samlUsernameVariable']);
		$ipskISEDB->setGlobalSetting("saml-settings","ldap-source-directory", $sanitizedInput['samlLdapSourceDirectory']);

		print true;
	}elseif($sanitizedInput['module-action'] == "complexity"){
		$passwordComplexity = $sanitizedInput['complexLowercase'] + $sanitizedInput['complexUppercase'] + $sanitizedInput['complexNumbers'] + $sanitizedInput['complexSpecial'] + $sanitizedInput['complexSimilar'];
		
		$ipskISEDB->setGlobalSetting("advanced-settings","password-complexity", $passwordComplexity);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "ersupdate"){
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","enabled", $sanitizedInput['ersEnabled']);
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","verify-ssl-peer", $sanitizedInput['ersVerifySsl']);
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","ersHost", $sanitizedInput['ersHost']);
		$ipskISEDB->setGlobalSetting("ise-ers-credentials","ersUsername", $sanitizedInput['ersUsername']);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "erspass"){	
		$ipskISEDB->setISEERSPassword($sanitizedInput['ersPassword']);
		
		print true;		
	}elseif($sanitizedInput['module-action'] == "mntupdate"){
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","enabled", $sanitizedInput['mntEnabled']);
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","verify-ssl-peer", $sanitizedInput['mntVerifySsl']);
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","mntHost", $sanitizedInput['mntHost']);
		$ipskISEDB->setGlobalSetting("ise-mnt-credentials","mntUsername", $sanitizedInput['mntUsername']);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "mntpass"){	
		$ipskISEDB->setISEMnTPassword($sanitizedInput['mntPassword']);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "smtpupdate"){
		$ipskISEDB->setGlobalSetting("smtp-settings","enabled", $sanitizedInput['smtpEnabled']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-hostname", $sanitizedInput['smtpHost']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-port", $sanitizedInput['smtpPort']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-username", $sanitizedInput['smtpUsername']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-fromaddress", $sanitizedInput['smtpFromAddress']);
		$ipskISEDB->setGlobalSetting("smtp-settings","smtp-encryption", $sanitizedInput['smtpEncryption']);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "smtppass"){	
		$ipskISEDB->setSMTPPassword($sanitizedInput['smtpPassword']);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "advancedupdate"){
		$ipskISEDB->setGlobalSetting("advanced-settings","enable-portal-psk-edit", $sanitizedInput['portalPskEditEnabled']);
		$ipskISEDB->setGlobalSetting("advanced-settings","enable-advanced-logging", $sanitizedInput['advancedLoggingSettings']);
		
		print true;
	}elseif($sanitizedInput['module-action'] == "loggingupdate"){
		$loggingLevel = $sanitizedInput['sqlLogging'] + $sanitizedInput['payloadLogging'] +	$sanitizedInput['debugLogging'] + $sanitizedInput['getLogging'] + $sanitizedInput['postLogging'] + $sanitizedInput['sessionLogging'] + $sanitizedInput['serverLogging'];
		$ipskISEDB->setGlobalSetting("platform-config", "logging-level", $loggingLevel);		
		
		print true;
	}else{
		print false;
	}
?>