<?php

function ipskReadCsvUpload($file) {
    $errors = [UPLOAD_ERR_INI_SIZE=>'CSV exceeds the server upload size limit.', UPLOAD_ERR_FORM_SIZE=>'CSV exceeds the form size limit.', UPLOAD_ERR_PARTIAL=>'CSV upload was incomplete. Retry the upload.', UPLOAD_ERR_NO_FILE=>'Choose a CSV file. If one was selected, it may exceed the request size limit.', UPLOAD_ERR_NO_TMP_DIR=>'Upload temporary directory is unavailable.', UPLOAD_ERR_CANT_WRITE=>'Server could not write the upload.', UPLOAD_ERR_EXTENSION=>'A server extension stopped the upload.'];
    $code = is_array($file) ? ($file['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
    if ($code !== UPLOAD_ERR_OK) { return ['error'=>$errors[$code] ?? 'Invalid upload status. Retry the upload.']; }
    $path = $file['tmp_name'] ?? null;
    if (!is_string($path) || $path === '' || !is_file($path) || !is_readable($path)) { return ['error'=>'Uploaded CSV could not be read. Retry the upload.']; }
    $contents = @file_get_contents($path);
    if ($contents === false) { return ['error'=>'Uploaded CSV could not be read. Retry the upload.']; }
    return ['contents'=>$contents];
}
function ipskUploadFailure($message) {
    return ['result'=>false,'message'=>$message,'recordsprocessed'=>0,'validitems'=>0,'filtereditems'=>0,'invaliditems'=>0,'invalidchar'=>0,'uploadkey'=>''];
}


function ipskStorePendingImport($records) {
    foreach (array_keys($_SESSION['bulk-import'] ?? []) as $key) {
        if (($_SESSION['bulk-import-expiry'][$key] ?? 0) <= time()) {
            unset($_SESSION['bulk-import'][$key], $_SESSION['bulk-import-expiry'][$key]);
        }
    }
    do { $token = bin2hex(random_bytes(32)); } while (isset($_SESSION['bulk-import'][$token]));
    $_SESSION['bulk-import'][$token] = $records;
    $_SESSION['bulk-import-expiry'][$token] = time() + 1800;
    return $token;
}
function ipskTakePendingImport($token) {
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) { return false; }
    $records = ($_SESSION['bulk-import-expiry'][$token] ?? 0) > time() ? ($_SESSION['bulk-import'][$token] ?? false) : false;
    unset($_SESSION['bulk-import'][$token], $_SESSION['bulk-import-expiry'][$token]);
    return $records;
}


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
 *@contributor	Nick Ciesinski (nciesins@cisco.com)
 */
	$maxModuleKeywordLength = 15;
		
	$subModuleRegEx = "/^(?:create|new|add|edit|view|delete|enable|disable|extend|modify|update|suspend|activate|groups|updategroups|pass|updatepass|authzprofile|bulk|bulkimport)$/";
	
	function ipskSessionHandler($scope = "portal"){
		
        // Separate cookies even when the virtual hosts share PHP session storage.
        session_name(['admin'=>'IPSKADMIN','installer'=>'IPSKINSTALLER'][$scope] ?? 'IPSKPORTAL');
        session_start([
            'use_strict_mode' => 1,
            'use_only_cookies' => 1,
            'cookie_httponly' => 1,
            'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'cookie_samesite' => 'Lax',
        ]);
        ipskProtectRequest();

		if(!isset($_SESSION['creationTime'])) {
			$_SESSION['creationTime'] = time();
			$_SESSION['authenticationGranted'] = false;
			$_SESSION['authorizationGranted'] = false;
			$_SESSION['authenticationTimestamp'] = false;
			$_SESSION['authorizationTimestamp'] = false;
			
			$octets = explode(".",$_SERVER['REMOTE_ADDR']);
			$_SESSION['sessionID'] = sprintf("%02x%02x%02x%02x-%08x", $octets[0], $octets[1], $octets[2], $octets[3], time());
		}
	}

    function ipskLoginSessionCheck() {
        if (empty($_SESSION['loggedIn']) || empty($_SESSION['authenticationGranted']) ||
            empty($_SESSION['authorizationGranted']) || !isset($_SESSION['logonDN'], $_SESSION['logonUsername'])) {
            return false;
        }
        $now = time();
        // A 30-minute idle window and 12-hour absolute lifetime apply to all portals.
        $started = $_SESSION['authenticationTimestamp'] ?? $now;
        $last = $_SESSION['lastActivity'] ?? $started;
        if ((isset($_SESSION['logoutTimer']) && (!is_numeric($_SESSION['logoutTimer']) || $_SESSION['logoutTimer'] <= $now)) ||
            $started + 43200 <= $now || $last + 1800 <= $now) {
            ipskResetIdentity();
            return false;
        }
        $_SESSION['authenticationTimestamp'] = $started;
        $_SESSION['lastActivity'] = $now;
        return true;
    }

    function ipskRotateSession() {
        if (session_status() === PHP_SESSION_ACTIVE && !session_regenerate_id(true)) {
            ipskResetIdentity();
            throw new RuntimeException('Unable to establish a secure session');
        }
    }

    function ipskResetIdentity() {
        // Only navigation context survives a new authentication attempt.
        $_SESSION = array_intersect_key($_SESSION ?? [], array_flip(['creationTime', 'sessionID', 'portalSettings', 'portalGET']));
        $_SESSION['authenticationGranted'] = false;
        $_SESSION['authorizationGranted'] = false;
        $_SESSION['loggedIn'] = false;
        $_SESSION['portalAuthorization'] = array_fill_keys(['viewall', 'viewgroup', 'viewowned', 'create', 'bulkcreate', 'viewallPSK', 'viewgroupPSK', 'viewownedPSK'], false);
        $_SESSION['portalAuthorization']['viewallDn'] = '';
    }






    function ipskCsrfToken() {
        if (empty($_SESSION['csrfToken'])) $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
        return $_SESSION['csrfToken'];
    }

    function ipskCsrfValid($token) {
        return is_string($token) && isset($_SESSION['csrfToken']) && hash_equals($_SESSION['csrfToken'], $token);
    }

    function ipskCsrfHtml($html, $token) {
        $field = '<input type="hidden" name="csrfToken" value="'.ipskHtml($token).'">';
        $html = preg_replace('/(<form\b[^>]*\bmethod=["\']?post["\']?[^>]*>)/i', '$1'.$field, $html);
        $json = json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $script = '<script>(function(){const token='.$json.';const open=XMLHttpRequest.prototype.open;const send=XMLHttpRequest.prototype.send;XMLHttpRequest.prototype.open=function(method,url){this.ipskCsrfOrigin=new URL(url,location.href).origin===location.origin;this.ipskCsrfMethod=method.toUpperCase();return open.apply(this,arguments);};XMLHttpRequest.prototype.send=function(){if(this.ipskCsrfOrigin&&!/^(GET|HEAD|OPTIONS)$/.test(this.ipskCsrfMethod)){this.setRequestHeader("X-CSRF-Token",token);}return send.apply(this,arguments);};})();</script>';
        return preg_replace('/(<head\b[^>]*>)/i', '$1'.$script, $html, 1);
    }

    function ipskProtectRequest() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET','HEAD','OPTIONS'], true) &&
            !ipskCsrfValid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrfToken'] ?? null)) {
            http_response_code(403);
            exit('The security token is missing or expired. Reload the page and try again.');
        }
        $token = ipskCsrfToken();
        if (PHP_SAPI !== 'cli') {
            ob_start(function ($html) use ($token) {
                foreach (headers_list() as $header) {
                    if (stripos($header, 'Content-Type:') === 0 && stripos($header, 'text/html') === false) return $html;
                }
                return ipskCsrfHtml($html, $token);
            });
        }
    }

    function ipskCanonicalMac($mac) {
        if (!is_string($mac) || !preg_match('/\A(?:[0-9a-f]{12}|(?:[0-9a-f]{2}:){5}[0-9a-f]{2}|(?:[0-9a-f]{2}-){5}[0-9a-f]{2})\z/iD',$mac)) return false;
        return implode(':',str_split(strtoupper(str_replace([':', '-'],'',$mac)),2));
    }

    function ipskPortalParameters($input) {
        $out = [];
        foreach (['client_mac', 'sessionId', 'redirect'] as $key) {
            if (!isset($input[$key])) continue;
            $value = $input[$key];
            if (!is_string($value)) return false;
            if ($key === 'client_mac') {
                if (!preg_match('/\A(?:[0-9a-f]{2}[:-]){5}[0-9a-f]{2}\z/iD', $value)) return false;
                $value = strtoupper(str_replace('-', ':', $value));
            } elseif ($key === 'sessionId') {
                if (!preg_match('/\A[a-zA-Z0-9_.:-]{1,128}\z/D', $value)) return false;
            } elseif ($value !== '' && (!filter_var($value, FILTER_VALIDATE_URL) ||
                !in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http','https'], true) ||
                preg_match('/[\x00-\x20\x7f]/', $value))) return false;
            $out[$key] = $value;
        }
        return $out;
    }

    function ipskPortalQuery($portalId, $parameters) {
        return http_build_query(['portalId'=>$portalId] + $parameters, '', '&', PHP_QUERY_RFC3986);
    }

    function ipskUsername($value) {
        return is_string($value) && trim($value) !== '' && preg_match('/\A[^\x00-\x1f\x7f]{1,255}\z/uD',$value) ? $value : false;
    }

    function ipskPskPlain($stored) {
        return strncmp($stored, 'psk=', 4) === 0 ? substr($stored, 4) : $stored;
    }

    function ipskHtml($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    function ipskRefreshPortalAuthorization($database, $portalId) {
        $grants = $database->getPortalAuthGroups($portalId);
        $matched = []; $bits = 0;
        foreach ($grants ?: [] as $grant) {
            if (!is_array($grant) || !in_array($grant['groupDn'], $_SESSION['memberOf'] ?? [], true)) continue;
            $matched[] = $grant;
            $bits |= $grant['groupPermissions'];
        }
        if (!$matched) { ipskResetIdentity(); return false; }
        $matched['count'] = count($matched);
        $_SESSION['authorizationGroups'] = $matched;
        foreach (['create'=>512,'bulkcreate'=>2048,'viewall'=>4,'viewgroup'=>2,'viewowned'=>1,'viewallPSK'=>12,'viewgroupPSK'=>10,'viewownedPSK'=>9] as $name=>$mask) {
            $_SESSION['portalAuthorization'][$name] = false;
            foreach ($matched as $grant) {
                if (is_array($grant) && ($grant['groupPermissions'] & $mask) === $mask) $_SESSION['portalAuthorization'][$name] = true;
            }
        }
        $_SESSION['portalAuthorization']['viewallDn'] = '';
        foreach ($matched as $grant) {
            if (is_array($grant) && ($grant['groupPermissions'] & 4)) $_SESSION['portalAuthorization']['viewallDn'] = $grant['groupDn'];
        }
        $_SESSION['authorizedEPGroups'] = $database->getEndPointGroupAuthorizations($portalId, $matched) ?: ['count'=>0];
        $_SESSION['authorizedWirelessNetworks'] = $database->getPortalWirelessNetworkAuthorization($portalId, $matched);
        return true;
    }

    function ipskEndpointPermission($database, $associationId, $action = 0) {
        $permissions = $database->getEndPointAssociationPermissions($associationId, $_SESSION['authorizationGroups'] ?? [], $_SESSION['portalSettings']['id']);
        $endpoint = false;
        foreach ($permissions ?: [] as $row) {
            if (!is_array($row)) continue;
            $bits = (int)$row['groupPermissions'];
            if (($bits & $action) !== $action) continue;
            if ($bits & 6) return true;
            if ($bits & 1) {
                $endpoint = $endpoint ?: $database->getEndpointByAssociationId($associationId);
                if ($endpoint && $endpoint['createdBy'] === $_SESSION['logonSID']) return true;
            }
        }
        return false;
    }


    function ipskMergeGroupGrants($rows) {
        $groups = [];
        foreach ($rows as $row) {
            if (!is_array($row)) continue;
            $id = $row['endpointGroupId'];
            if (!isset($groups[$id])) $groups[$id] = $row + ['createMaximum'=>null,'bulkMaximum'=>null];
            $groups[$id]['groupPermissions'] |= $row['groupPermissions'];
            foreach ([512=>'createMaximum',2048=>'bulkMaximum'] as $permission=>$field) {
                if (!($row['groupPermissions'] & $permission)) continue;
                $old = $groups[$id][$field]; $new = (int)$row['maxDevices'];
                $groups[$id][$field] = $old === null ? $new : (($old === 0 || $new === 0) ? 0 : max($old,$new));
            }
        }
        $result=[];
        foreach ($groups as $row) {
            $row['maxDevices'] = $row['createMaximum'] ?? 0;
            $row['bulkMaxDevices'] = $row['bulkMaximum'] ?? 0;
            $row['viewPermissions'] = $row['groupPermissions'] & 15;
            $row['advancedPermissions'] = $row['groupPermissions'] & 2032;
            $result[]=$row;
        }
        $result['count']=count($result);
        return $result;
    }

    function ipskQuotaAvailable($count, $maximum, $incoming = 1) {
        return $count !== false && is_numeric($count) && $count >= 0 && is_numeric($maximum) && $maximum >= 0 &&
            $incoming >= 0 && ($maximum == 0 || $count + $incoming <= $maximum);
    }

    function ipskCreationGrant($database, $groupId, $permission = 512) {
        if (!filter_var($groupId, FILTER_VALIDATE_INT) || $groupId < 1 || empty($_SESSION['portalSettings']['id'])) return false;
        $groups = $database->getEndPointGroupAuthorizations($_SESSION['portalSettings']['id'], $_SESSION['authorizationGroups'] ?? []);
        $_SESSION['authorizedEPGroups'] = $groups ?: ['count' => 0];
        $grant = false;
        for ($i = 0; $i < ($groups['count'] ?? 0); $i++) {
            if ($groups[$i]['endpointGroupId'] == $groupId && ($groups[$i]['groupPermissions'] & $permission) === $permission) {
                $grant = $groups[$i];
            }
        }
        if (!$grant) return false;
        if ($permission === 2048) $grant['maxDevices'] = $grant['bulkMaxDevices'] ?? $grant['maxDevices'];
        $count = $database->getUserEndpointCount($groupId, $_SESSION['logonSID']);
        if (!ipskQuotaAvailable($count, $grant['maxDevices'])) return false;
        return $grant;
    }

    function ipskAdminSessionCheck() {
        return ipskLoginSessionCheck() && ($_SESSION['adminAuthorized'] ?? false) === true;
    }

    // Routes are application capabilities, never filesystem paths supplied by callers.
    function ipskPortalRoute($module, $template, $route) {
        $routes = [
            'captiveportal' => ['index', 'login', 'logoff', 'enroll', 'create', 'error', 'performcoa'],
            'sponsorportal' => ['index', 'login', 'logoff', 'sponsor', 'manage', 'create', 'edit',
                'view', 'delete', 'activate', 'suspend', 'extend', 'bulk', 'bulkimport',
                'bulkupdate', 'fileupload', 'query'],
        ];
        if (!is_string($module) || !isset($routes[$module]) || $template !== 'default' ||
            !is_string($route) || !in_array($route, array_map(function ($name) { return $name.'.php'; }, $routes[$module]), true)) {
            return false;
        }
        return __DIR__.'/../portals/'.$module.'/'.$template.'/'.substr($route, 0, -4).'.inc.php';
    }

    function ipskPortalAsset($module, $route) {
        if (!in_array($module, ['captiveportal', 'sponsorportal'], true) || !is_string($route) ||
            !preg_match('~\A(?:scripts|styles|images)/[a-zA-Z0-9_.-]+\.(?:css|js|svg|gif|png|jpg|ico|jpeg)\z~D', $route)) {
            return false;
        }
        $base = realpath(__DIR__.'/../portals/'.$module.'/commonfiles');
        $path = realpath($base.'/'.$route);
        return $base && $path && strncmp($path, $base.DIRECTORY_SEPARATOR, strlen($base) + 1) === 0 ? $path : false;
    }

	function convertBinSID($binSid) {
		//Break apart the byte stream
		$stringSid = unpack('Crevision/x/nauthhigh/Nauthlow/V*', $binSid);
		
		//Rebuild in String Form
		$secuirtyId = sprintf("S-%u-%d-%u-%u-%u-%u-%u", $stringSid['revision'], ($stringSid['authhigh'] << 32) + $stringSid['authlow'], $stringSid[1], $stringSid[2], $stringSid[3], $stringSid[4], $stringSid[5]);

		return $secuirtyId;
	}

	function generateGuid(){
		//Generate new Guid with cryptographically secure pseudo-random integers
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', random_int(0, 65535), random_int(0, 65535), random_int(0, 65535), random_int(16384, 20479), random_int(32768, 49151), random_int(0, 65535), random_int(0, 65535), random_int(0, 65535));
	}

	function sanitizeGetModuleInput($RegEx){
		
		// String keys to sanitize seperatly due to deprecation of filter FILTER_SANITIZE_STRING
		$stringKeysToSanitize = [
			'ssidName',
			'ssidDescription',
			'authzPolicyName',
			'authzPolicyDescription',
			'ciscoAVPairPSK',
			'epGroupName',
			'epGroupDescription',
			'sponsorGroupName',
			'sponsorGroupDescription',
			'groupName',
			'groupDn',
			'portalName',
			'authzProfileName',
			'description',
			'hostname',
			'userName',
			'fullName',
			'email',
			'password',
			'confirmpassword',
			'adConnectionName',
			'adServer',
			'adDomain',
			'adUsername',
			'adBaseDN',
			'presharedKey',
			'emailAddress',
			'macAddress',
			'endpointDescription',
			'fullName',
			'hostname',
			'ersHost',
			'ersUsername',
			'ersPassword',
			'mntHost',
			'mntHostSecondary',
			'mntUsername',
			'mntPassword',
			'adminPortalHostname',
			'smtpHost',
			'smtpUsername',
			'smtpPassword',
			'smtpFromAddress',
			'smtpEncryption',
			'samlUsernameVariable',	
			'vlan',
			'dacl',
		];

		$arguments = array(
			'module'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-Z]|[a-z]|[0-9]){1,50}$/')
								),
			'sub-module'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
									  'flags'     => '', 
									  'options'   => array('regexp' => $RegEx)
								),
			'module-action'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-Z]|[a-z]){1,15}$/')
								),
			'id'	=>	FILTER_VALIDATE_INT,
			'logoff'	=>	FILTER_VALIDATE_BOOLEAN,
			'action'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-Z]|[a-z]|[0-9]|_){1,25}$/')
								),
			'groupUuid'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^(?:[A-F]|[a-f]|[0-9]){8}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){12}$/')
								),
			'uploadkey'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
								  'flags'     => '' ,
								  'options'   => array('regexp' => '/^[a-f0-9]{64}$/D')
								),
			'confirmaction'	=>	FILTER_VALIDATE_INT,
			'wirelessSSID'	=>	FILTER_VALIDATE_INT,
			'ssidName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ssidDescription'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzPolicyName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzPolicyDescription'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'termLengthSeconds'	=>	FILTER_VALIDATE_INT,
			'ciscoAVPairPSK'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'pskLength'	=>	FILTER_VALIDATE_INT,
			'pskType'	=>	FILTER_VALIDATE_INT,
			'pskMode'	=>	FILTER_VALIDATE_INT,
			'epGroupName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'epGroupDescription'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzTemplate'	=>	FILTER_VALIDATE_INT,
			'notificationPermission'	=>	FILTER_VALIDATE_INT,
			'maxDevices'	=>	FILTER_VALIDATE_INT,
			'sponsorGroupName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'sponsorGroupDescription'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'sponsorGroupAuthType'	=>	FILTER_VALIDATE_INT,
			'sponsorGroupType'	=>	FILTER_VALIDATE_INT,
			'sponsorPortalType'	=>	FILTER_VALIDATE_INT,
			'suspendCheck'	=>	FILTER_VALIDATE_INT,
			'unsuspendCheck'	=>	FILTER_VALIDATE_INT,
			'extendCheck'	=>	FILTER_VALIDATE_INT,
			'deleteCheck'	=>	FILTER_VALIDATE_INT,
			'editCheck'	=>	FILTER_VALIDATE_INT,
			'createCheck'	=>	FILTER_VALIDATE_INT,
			'viewPassCheck'	=>	FILTER_VALIDATE_INT,
			'viewPermission'	=>	FILTER_VALIDATE_INT,
			'resetPskCheck'	=>	FILTER_VALIDATE_INT,
			'groupName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'groupDn'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'portalName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'authzProfileName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'description'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'hostname'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'tcpPort'	=>	FILTER_VALIDATE_INT,
			'permission'	=>	FILTER_VALIDATE_INT,
			'groupType'	=>	FILTER_VALIDATE_INT,
			'authDirectory'	=>	FILTER_VALIDATE_INT,
			'inputUsername' => array('filter'	=>	FILTER_CALLBACK,
									 'options'	=>	function ($value) {
										return ipskUsername($value);
									 }
								),
			'userName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'fullName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'email'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'password'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'confirmpassword'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adConnectionName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adServer'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adDomain'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adUsername'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adBaseDN'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'adSecure'	=>	FILTER_VALIDATE_INT,
			'directoryType' =>	FILTER_VALIDATE_INT,
			'associationGroup'	=>	FILTER_VALIDATE_INT,
			'editAssociation'	=>	FILTER_VALIDATE_INT,
			'editPSK'	=>	FILTER_VALIDATE_INT,
			'logPurgeInterval'	=>	FILTER_VALIDATE_INT,
			'usePortalDescription'	=>	FILTER_VALIDATE_INT,
			'presharedKey'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'emailAddress'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'macAddress'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'endpointDescription'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'fullName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'hostname'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'protocol'	=>	FILTER_VALIDATE_INT,
			'portalPort'	=>	FILTER_VALIDATE_INT,
			'ersHost'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ersUsername'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ersPassword'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpPassword'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'ersEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'ersVerifySsl'	=>	FILTER_VALIDATE_BOOLEAN,
			'mntHost'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntHostSecondary'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntUsername'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntPassword'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'mntEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'mntVerifySsl'	=>	FILTER_VALIDATE_BOOLEAN,
			'fullAuthZUpdate'	=>	FILTER_VALIDATE_BOOLEAN,
			'fullAuthZUpdateVLANdACL'	=>	FILTER_VALIDATE_BOOLEAN,
			'adminPortalHostname'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'strict-hostname'	=>	FILTER_VALIDATE_BOOLEAN,
			'redirect-hostname'	=>	FILTER_VALIDATE_BOOLEAN,
			'smtpHost'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpPort'	=>	FILTER_VALIDATE_INT,
			'smtpUsername'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpFromAddress'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpEncryption'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'smtpEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'portalPskEditEnabled'	=>	FILTER_VALIDATE_BOOLEAN,
			'advancedLoggingSettings'	=>	FILTER_VALIDATE_BOOLEAN,
			'portalPskEditCheck'	=>	FILTER_VALIDATE_INT,
			'bulkCreateCheck'	=>	FILTER_VALIDATE_INT,
			'bulkImportType'	=>	FILTER_VALIDATE_INT,
			'sqlLogging'	=>	FILTER_VALIDATE_INT,
			'payloadLogging'	=>	FILTER_VALIDATE_INT,
			'debugLogging'	=>	FILTER_VALIDATE_INT,
			'getLogging'	=>	FILTER_VALIDATE_INT,
			'postLogging'	=>	FILTER_VALIDATE_INT,
			'sessionLogging'	=>	FILTER_VALIDATE_INT,
			'serverLogging'	=>	FILTER_VALIDATE_INT,
			'complexLowercase'	=>	FILTER_VALIDATE_INT,
			'complexUppercase'	=>	FILTER_VALIDATE_INT,
			'complexNumbers'	=>	FILTER_VALIDATE_INT,
			'complexSpecial'	=>	FILTER_VALIDATE_INT,
			'complexSimilar'	=>	FILTER_VALIDATE_INT,
			'aupAccept'	=>	FILTER_VALIDATE_BOOLEAN,
			'samlEnabled' => FILTER_VALIDATE_BOOLEAN,
			'samlLdapSource' => FILTER_VALIDATE_BOOLEAN,
			'samlHeaders' => FILTER_VALIDATE_BOOLEAN,
			'samlUsernameVariable'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'samlLdapSourceDirectory' => FILTER_VALIDATE_INT,
			'ldapSSLCheck' => FILTER_VALIDATE_BOOLEAN,
			'nestedGroups' => FILTER_VALIDATE_BOOLEAN,
			'logDisplay' =>	FILTER_VALIDATE_INT,
			'vlan'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			'dacl'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
		);
		
		$mysanitizedInputs = filter_input_array(INPUT_POST, $arguments);

		// Added sanitization method for strings due to deprecation of filter FILTER_SANITIZE_STRING
		if (is_iterable($mysanitizedInputs)) {
			foreach ($mysanitizedInputs as $key => $value) {
				if (in_array($key, $stringKeysToSanitize)) {
					if (!in_array($key, ['password', 'confirmpassword', 'ersPassword', 'mntPassword', 'smtpPassword'])) {
						$mysanitizedInputs[$key] = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
					} else {
						// Just trim to remove whitespace, preserve all characters
						$mysanitizedInputs[$key] = trim($value);
					}
				}
			}
		}

        foreach (['inputUsername','userName'] as $key) {
            if (array_key_exists($key,$_POST)) $mysanitizedInputs[$key] = ipskUsername($_POST[$key]);
        }
        // Secrets are validated as secrets, never trimmed, stripped or HTML-encoded before storage.
        foreach (['password','confirmpassword','ersPassword','mntPassword','smtpPassword','adPassword','ciscoAVPairPSK','presharedKey'] as $key) {
            if (array_key_exists($key,$_POST)) $mysanitizedInputs[$key] = is_string($_POST[$key]) ? $_POST[$key] : false;
        }
		return $mysanitizedInputs;
	}

	function sanitizeGetDataInput($dataCommandRegEx, $dataDataSetRegEx, $dataInputName, $dataInputFilter){

		$stringKeysToSanitize = [
			'authzProfileName',
		];
			
		$arguments = array(
			'data-command'	=>	array('filter' => FILTER_VALIDATE_REGEXP, 'flags' => '' , 'options' => array('regexp' => $dataCommandRegEx)),
			'data-set'	=>	array('filter' => FILTER_VALIDATE_REGEXP, 'flags' => '' , 'options' => array('regexp' => $dataDataSetRegEx)),
			'pskLength'	=>	FILTER_VALIDATE_INT,
			'authzProfileName'	=>	array('filter'	=>	FILTER_UNSAFE_RAW,
								  'flags'	=>	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
								  ),
			  'groupUuid'	=>	array('filter'    => FILTER_VALIDATE_REGEXP,
			  'flags'     => '' ,
			  'options'   => array('regexp' => '/^(?:[A-F]|[a-f]|[0-9]){8}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){4}-(?:[A-F]|[a-f]|[0-9]){12}$/')
			),
			"$dataInputName" =>	$dataInputFilter
		);
		
		$mysanitizedInputs = filter_input_array(INPUT_POST, $arguments);

		if (is_iterable($mysanitizedInputs)) {
			foreach ($mysanitizedInputs as $key => $value) {
				if (in_array($key, $stringKeysToSanitize)) {
					$mysanitizedInputs[$key] = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
				} 
			}
		}

		return $mysanitizedInputs;
		
	}

	/* START: Functions for sorting the returned EP Groups
	 * array from ISE ERS.
	 */

	/* Support function for sorting ISE Return Results.
	 * This was created to sort the Endpoint Identity Groups array
	 */
	function ____alphaSorting($left, $right){
		$result = strcmp($left["name"], $right["name"]);
		return $result;
	}

	/* Function that checks the input and validates it meets the criteria.
	 * This returns the array back; however, the 'name' field is sorted
	 */
	function arraySortAlpha($inputArray){
		if(isset($inputArray['SearchResult']['total'])){
			if($inputArray['SearchResult']['total'] > 7){
				if(isset($inputArray['SearchResult']['resources'][0]['name'])){
					$mySortedResources = $inputArray['SearchResult']['resources'];
					usort($mySortedResources,'____alphaSorting');
					$inputArray['SearchResult']['resources'] = $mySortedResources;

					return $inputArray;

				}else{
					return $inputArray;
				}
			}else{
				return $inputArray;
			}
		}else{
			return $inputArray;
		}
	}
	/* END: Functions for sorting the returned EP Groups
	 * array from ISE ERS.
	 */
?>