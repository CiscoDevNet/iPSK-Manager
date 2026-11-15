<?php
// Login notices are plain text. Never execute or render administrator-supplied HTML.
function ipskLoginNoticeInput($enabled, $text) {
    if (!in_array($enabled, [0,1,'0','1'], true) || !is_string($text) ||
        strlen($text) > 20000 || !preg_match('//u', $text) || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', $text)) return false;
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    if ((int)$enabled === 1 && preg_match('/\A\s*\z/u', $text)) return false;
    return ['enabled'=>(bool)$enabled, 'text'=>$text];
}
function ipskLoginNoticeFields($notice) {
    if (!$notice['enabled']) return '';
    $text = ipskHtml($notice['text']);$version = ipskHtml($notice['revision']);
    return '<div class="text-start my-3"><details class="border rounded p-2 mb-3"><summary class="fw-bold">Read the login notice</summary>'
        .'<div class="mt-2" style="white-space:pre-wrap;overflow-wrap:anywhere;max-height:20rem;overflow:auto" tabindex="0">'.$text.'</div></details>'
        .'<input type="hidden" name="loginNoticeRevision" value="'.$version.'">'
        .'<div class="form-check"><input class="form-check-input" type="checkbox" name="loginNoticeAccepted" id="loginNoticeAccepted" value="1" required>'
        .'<label class="form-check-label" for="loginNoticeAccepted">I have read and agree to the login notice.</label></div></div>';
}
function ipskLoginNoticeButtonScript() {
    return '<script>(function(){const box=document.getElementById("loginNoticeAccepted");if(!box)return;const form=box.form;const button=form.querySelector("button[type=submit]");if(!button)return;const update=()=>{button.disabled=!box.checked;};box.addEventListener("change",update);window.addEventListener("pageshow",update);update();})();</script>';
}
function ipskLoginNoticePage($notice, $action, $home, $sso, $message) {
    $action=ipskHtml($action);$home=ipskHtml($home);$message=ipskHtml($message);
    $fields=ipskLoginNoticeFields($notice);
    $token=ipskHtml(ipskCsrfToken());
    $controls=$sso ? '<form method="post" action="'.$action.'"><input type="hidden" name="csrfToken" value="'.$token.'">'.$fields.'<button type="submit" class="btn btn-primary">Continue to sign in</button></form>'
        : '<a class="btn btn-primary" href="'.$home.'">Return to sign in</a>';
    print '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login notice</title><link href="styles/bootstrap.min.css" rel="stylesheet"></head><body><main class="container py-5" style="max-width:42rem"><h1 class="h3">Login notice</h1><p role="alert">'.$message.'</p>'.$controls.'</main>'.ipskLoginNoticeButtonScript().'</body></html>';
}
function ipskLoginNoticeGate($database, $action, $home, $sso) {
    try { $notice=$database->getLoginNotice(); }
    catch (Throwable $error) { ipskResetIdentity();http_response_code(503);print 'The login notice could not be loaded. Please try again or contact your administrator.';return false; }
    if (!$notice['enabled']) return $notice;
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' &&
        ipskCsrfValid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrfToken'] ?? null) &&
        ($_POST['loginNoticeAccepted'] ?? null) === '1' && is_string($_POST['loginNoticeRevision'] ?? null) &&
        hash_equals($notice['revision'], $_POST['loginNoticeRevision'])) return $notice;
    // A failed/new login attempt must not keep the previous identity's authority.
    $token=ipskCsrfToken();ipskResetIdentity();$_SESSION['csrfToken']=$token;
    http_response_code(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 400 : 200);
    ipskLoginNoticePage($notice,$action,$home,$sso,'Please review and agree to the current login notice before signing in. If it has changed, review it again.');
    return false;
}
function ipskRecordLoginNoticeAcceptance($database, $scope, $notice) {
    if (!$notice || !$notice['enabled']) return;
    // Record only the revision and authenticated identity, never login credentials.
    $database->addLogEntry('REQUEST:SUCCESS;ACTION:LOGIN-NOTICE-ACCEPTED;SCOPE:'.$scope.';REVISION:'.$notice['revision'].';SID:'.($_SESSION['logonSID'] ?? '').';',__FILE__,__FUNCTION__,'','',__LINE__,'');
}
function ipskLoginNoticeEditor($database) {
    if (!$database->loginNoticesAvailable()) return '<p class="text-muted">Apply schema 14 to configure an optional login notice.</p>';
    $notice=$database->getLoginNotice();$id='login-notice-editor';
    $state=$notice['enabled']?'Enabled':'Disabled';
    $checked=$notice['enabled']?' checked':'';$text=ipskHtml($notice['text']);$idJson=json_encode($id);
    return <<<HTML
<details id="$id" class="border rounded mt-4 text-start">
<summary class="p-3 bg-light rounded"><span class="fw-semibold">Shared login notice</span> <span class="small text-muted ms-2" data-notice-state>$state</span></summary>
<div class="p-3 border-top">
<div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="$id-enabled"$checked><label class="form-check-label" for="$id-enabled">Require agreement before sign-in</label></div>
<label class="form-label" for="$id-text">Notice text</label><textarea id="$id-text" class="form-control" rows="4" aria-describedby="$id-help">$text</textarea>
<p class="form-text" id="$id-help">Plain text, up to 20,000 UTF-8 bytes. Saved separately from other settings. Applies to admin, sponsor and captive sign-ins, including SSO.</p>
<button type="button" class="btn btn-primary btn-sm" data-notice-save>Save login notice</button><p class="mt-2 mb-0" role="status" aria-live="polite" data-notice-status></p>
</div>
</details>
<script>(function(){const root=document.getElementById($idJson);const button=root.querySelector('[data-notice-save]');const status=root.querySelector('[data-notice-status]');button.addEventListener('click',function(){const enabled=root.querySelector('input[type=checkbox]').checked;button.disabled=true;status.textContent='Saving…';$.ajax({url:'ajax/getmodule.php',type:'POST',data:{module:'sysconfig','sub-module':'loginnotice',noticeEnabled:enabled?'1':'0',noticeText:root.querySelector('textarea').value},success:function(data){const saved=typeof data==='string'&&data.trim()==='Login notice saved.';status.textContent=saved?'Login notice saved.':'Could not save the notice. Reload and try again.';if(saved)root.querySelector('[data-notice-state]').textContent=enabled?'Enabled':'Disabled';},error:function(xhr){status.textContent=xhr.status===400?xhr.responseText:'Could not save the notice. Reload and try again.';},complete:function(){button.disabled=false;}});});})();</script>
HTML;
}
