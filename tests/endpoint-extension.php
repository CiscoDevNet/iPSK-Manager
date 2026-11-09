<?php
// Run with: php tests/endpoint-extension.php
require_once __DIR__.'/../supportfiles/include/iPSKManagerFunctions.php';
require_once __DIR__.'/../supportfiles/include/iPSKManagerDatabase.php';

error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

class ExtensionDatabaseStub {
    public $cap;
    public $permissions = 128;
    public $exists = true;
    public $writeResult = true;
    public $writes = [];
    public $logs = [];

    function getEndPointAssociationByEndpoint($id) {
        check($id == 42, 'POST must look up the endpoint ID');
        return $this->exists ? 7 : false;
    }

    function getEndPointAssociationPermissions($id, $groups, $portal) {
        return [['groupPermissions' => $this->permissions]];
    }

    function getEndpointByAssociationId($id) {
        if (!$this->exists) {
            return false;
        }
        check($id == 7, 'The group limit must be loaded using the association ID');
        return ['endpointId' => 42, 'termLengthSeconds' => $this->cap,
            'macAddress' => '001122334455', 'fullName' => 'Test User'];
    }

    function extendEndpoint($id, $expiration, $sid) {
        $this->writes[] = [$id, $expiration, $sid];
        return $this->writeResult;
    }

    function generateLogData($data) { return ''; }
    function addLogEntry($message, ...$context) { $this->logs[] = $message; }
}

function renderExtension($template, $database, $term, $confirm = true, $cachedCap = null) {
    $_SESSION = ['loggedIn' => true, 'logonDN' => 'test', 'logonUsername' => 'test',
        'authenticationGranted' => true, 'authorizationGranted' => true, 'adminAuthorized' => true,
        'logonSID' => 'test-sid', 'authorizationGroups' => [], 'portalSettings' => ['id' => 3]];
    if ($cachedCap !== null) {
        $_SESSION['extendEndpointMaxTerm'] = $cachedCap;
    }
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $portalId = 'test-portal';
    $ipskISEDB = $database;
    $sanitizedInput = ['id' => $confirm ? 42 : 7, 'confirmaction' => $confirm,
        'termLengthSeconds' => $term, 'macAddress' => '001122334455'];
    ob_start();
    try {
        include $template;
        return ob_get_contents();
    } finally {
        ob_end_clean();
    }
}

$templates = [
    'sponsor' => __DIR__.'/../supportfiles/portals/sponsorportal/default/extend.inc.php',
    'admin' => __DIR__.'/../supportfiles/adminportals/modules/endpoints/extend.inc.php',
];
$failures = 0;
$tests = 0;
function runTest($name, $test) {
    global $failures, $tests;
    $tests++;
    try {
        $test();
        echo "PASS: $name\n";
    } catch (Throwable $error) {
        $failures++;
        echo "FAIL: $name: {$error->getMessage()}\n";
    }
}

foreach ($templates as $portal => $template) {
    // Expected durations are measured from now; null means reject without a write.
    $cases = [
        'under cap' => [31536000, 86400, 86400],
        'no cached session cap' => [31536000, 86400, 86400, 'uncached'],
        'at cap' => [31536000, 31536000, 31536000],
        'over cap' => [31536000, 63072000, null],
        'unlimited selection on finite group uses cap' => [31536000, 0, 31536000],
        'unlimited group and selection never expire' => [0, 0, 0],
        'finite selection on unlimited group' => [0, 86400, 86400],
        'negative term' => [31536000, -86400, null],
        'invalid term' => [31536000, false, null],
        'missing endpoint' => [31536000, 86400, null, 'missing'],
        'database failure' => [31536000, 86400, null, 'failure'],
        'stale unlimited session cap' => [31536000, 63072000, null, 'stale'],
        'stale finite session cap' => [0, 172800, 172800, 'stale'],
    ];
    if ($portal === 'sponsor') {
        $cases['permission denied'] = [31536000, 86400, null, 'denied'];
    }
    foreach ($cases as $name => $case) {
        runTest("$portal: $name", function () use ($template, $portal, $case) {
            [$cap, $term, $expected] = $case;
            $mode = $case[3] ?? '';
            $db = new ExtensionDatabaseStub();
            $db->cap = $cap;
            $db->exists = $mode !== 'missing';
            $db->writeResult = $mode !== 'failure';
            $db->permissions = $mode === 'denied' ? 0 : 128;
            $before = time();
            $output = renderExtension($template, $db, $term, true, $mode === 'stale' ? ($cap ? 0 : 86400) : ($mode === 'uncached' ? null : $cap));
            $after = time();
            if ($expected === null) {
                check(count($db->writes) === ($mode === 'failure' ? 1 : 0), 'Invalid extension must not write');
                check(!str_contains($output, 'window.location') && !str_contains($output, "$('#mainContent').html(data)"), 'Failure must not redirect or refresh as success');
                check(count($db->logs) === 0, 'Failure must not log success');
                if ($mode !== 'denied') {
                    check(str_contains($output, 'alert('), 'Failure must display an error');
                }
            } else {
                check(count($db->writes) === 1, 'Valid extension must write once');
                [$id, $expiration, $sid] = $db->writes[0];
                check($id === 42 && $sid === 'test-sid', 'Must update the requested endpoint');
                check($expected === 0 ? $expiration === 0 : ($expiration >= $before + $expected && $expiration <= $after + $expected), 'Wrong expiration date');
                check($portal === 'sponsor' ? str_contains($output, 'window.location') : str_contains($output, "$('#mainContent').html(data)"), 'Success must return to management');
                if ($portal === 'sponsor') {
                    check(count($db->logs) === 1 && str_contains($db->logs[0], 'REQUEST:SUCCESS'), 'Successful extension must log once');
                }
            }
        });
    }

    foreach ([31536000, 0, 100000] as $cap) {
        runTest("$portal: options for cap $cap", function () use ($template, $cap) {
            $db = new ExtensionDatabaseStub();
            $db->cap = $cap;
            $output = renderExtension($template, $db, null, false);
            preg_match_all('/<option value="(\d+)"/', $output, $matches);
            check(count($matches[1]) > 0, 'Must offer valid extension terms');
            foreach ($matches[1] as $value) {
                check($cap == 0 || ($value > 0 && $value <= $cap), 'Must not offer terms beyond the current cap');
            }
            check(in_array('0', $matches[1], true) === ($cap == 0), 'Only unlimited groups offer No Expiration');
        });
    }
}

class ExtensionConnectionStub {
    public $result = true;
    public $throws = false;
    public $query;
    function real_escape_string($value) { return (string)$value; }
    function query($query) {
        $this->query = $query;
        if ($this->throws) {
            throw new RuntimeException('Database unavailable');
        }
        return $this->result;
    }
}

foreach (['success', 'false', 'exception'] as $mode) {
    runTest("database: $mode", function () use ($mode) {
        $db = new iPSKManagerDatabase();
        $connection = new ExtensionConnectionStub();
        $connection->result = $mode !== 'false';
        $connection->throws = $mode === 'exception';
        $property = new ReflectionProperty(iPSKManagerDatabase::class, 'dbConnection');
        $property->setAccessible(true);
        $property->setValue($db, $connection);
        check($db->extendEndpoint(42, 0, 'test-sid') === ($mode === 'success'), 'Must report the database result');
        check(str_contains($connection->query, "`accountExpired` = 'False', `expirationDate` = 0"), 'Unlimited extension must reset expired status and store zero');
    });
}

echo "$tests tests, $failures failures\n";
exit($failures ? 1 : 0);
