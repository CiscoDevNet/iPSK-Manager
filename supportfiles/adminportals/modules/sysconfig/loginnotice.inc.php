<?php
if (!function_exists('ipskAdminSessionCheck') || !ipskAdminSessionCheck()) {
    http_response_code(403);
    return;
}
if (ipskLoginNoticeInput($_POST['noticeEnabled'] ?? null,$_POST['noticeText'] ?? null) === false) {
    http_response_code(400);print 'Enter a nonempty notice when enabled, using plain text up to 20,000 UTF-8 bytes.';return;
}
if (!$ipskISEDB->loginNoticesAvailable()) { http_response_code(400);print 'Apply schema 14 before configuring login notices.';return; }
if (!$ipskISEDB->saveLoginNotice($_POST['noticeEnabled'],$_POST['noticeText'],$_SESSION['logonSID'])) {
    http_response_code(500);print 'Unable to save login notice.';return;
}
print 'Login notice saved.';
