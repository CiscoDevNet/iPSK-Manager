<?php
require __DIR__.'/review-bootstrap.php';
foreach (['../../adminportals/modules/internalusers/updatepass.php', '../index.php', '%2e%2e/index.php', 'index.php/../login.php', ['index.php']] as $route) {
    verify(ipskPortalRoute('sponsorportal', 'default', $route) === false, 'Traversal route accepted');
}
foreach (['sponsorportal', 'captiveportal'] as $module) {
    foreach (glob(__DIR__.'/../supportfiles/portals/'.$module.'/default/*.inc.php') as $file) {
        verify(realpath(ipskPortalRoute($module, 'default', basename($file, '.inc.php').'.php')) === realpath($file), 'Valid route denied');
    }
    verify(ipskPortalAsset($module, 'styles/bootstrap.min.css') !== false, 'Asset unavailable');
    verify(ipskPortalAsset($module, '../../../../../include/config.php') === false, 'Asset traversal');
}
verify(ipskPortalRoute('../adminportals', 'default', 'index.php') === false, 'Module traversal');
verify(ipskPortalRoute('sponsorportal', '../default', 'index.php') === false, 'Template traversal');
foreach ([[], validSession()] as $session) {
    $_SESSION = $session;
    foreach (glob(__DIR__.'/../supportfiles/adminportals/modules/*/*.inc.php') as $file) {
        ob_start(); include $file; $output = ob_get_clean();
        verify(http_response_code() === 403 && $output === '', 'Admin handler did not reject unauthenticated/non-admin include: '.$file);
    }
}
echo "PASS review 01: route allowlist, assets and every administrative handler guard\n";
