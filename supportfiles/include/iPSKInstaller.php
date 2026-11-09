<?php
// Only resources successfully created by this installation attempt may be removed.
function ipskCleanupInstallation($connection, $settings, $created) {
    $results = [];
    $actions = [];
    if (!empty($created['database'])) {
        $actions['Database Installation'] = 'DROP DATABASE `'.str_replace('`','``',$settings['databasename']).'`';
    }
    foreach (['manager'=>'dbusername','ise'=>'iseusername'] as $kind=>$field) {
        if (!empty($created[$kind])) {
            $actions[$kind === 'manager' ? 'Database User' : 'ISE Database User'] = "DROP USER '".$connection->real_escape_string($settings[$field])."'@'%'";
        }
    }
    foreach ($actions as $resource=>$sql) {
        try { $results[$resource] = $connection->query($sql) === true; }
        catch (Throwable $e) { $results[$resource] = false; }
    }
    return $results;
}

function ipskWriteInstallerConfig($path, $contents) {
    $temporary = @tempnam(dirname($path), '.ipsk-config-');
    if ($temporary === false) { return false; }
    try {
        if (@file_put_contents($temporary, $contents, LOCK_EX) !== strlen($contents)) { return false; }
        @chmod($temporary, 0600);
        // Publish only a complete file, and never overwrite a preexisting configuration.
        return @link($temporary, $path);
    } finally { @unlink($temporary); }
}
