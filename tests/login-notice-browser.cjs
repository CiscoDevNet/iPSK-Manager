// Run against the isolated PHP test server; see tests/README.md.
const assert = require('node:assert/strict');
const path = require('node:path');
const {chromium} = require(process.env.IPSK_PLAYWRIGHT_PATH || 'playwright');
const base = process.env.IPSK_NOTICE_TEST_URL;
if (!base || !/^http:\/\/(127\.0\.0\.1|localhost):\d+$/.test(base)) throw new Error('Use an isolated loopback fixture server');
(async () => {
  const browser = await chromium.launch({headless:true, executablePath:process.env.IPSK_CHROME_PATH});
  try {
    for (const javaScriptEnabled of [true,false]) {
      const context = await browser.newContext({javaScriptEnabled,viewport:{width:390,height:844}});
      await context.route('**/styles/bootstrap.min.css', route => route.fulfill({path:path.join(__dirname,'../adminportal/styles/bootstrap.min.css'),contentType:'text/css'}));
      await context.route('**/styles/signin.css', route => route.fulfill({path:path.join(__dirname,'../adminportal/styles/signin.css'),contentType:'text/css'}));
      await context.route('**/images/**', route => route.fulfill({status:204,body:''}));
      for (const kind of ['admin','sponsor','captive']) for (const sso of [false,true]) {
        const page=await context.newPage();const errors=[];page.on('pageerror',error=>errors.push(error.message));
        const options={kind,...(sso?{sso:'1',action:'login'}:{})};
        const url=base+'/login-notice-http-fixture.php?'+new URLSearchParams(options);
        await page.route('**/login.php*',route=>{
          const query=new URL(route.request().url()).searchParams;
          for(const [key,value] of Object.entries({...options,action:'login'}))query.set(key,value);
          return route.continue({url:base+'/login-notice-http-fixture.php?'+query});
        });
        await page.goto(url);
        const box=page.locator('#loginNoticeAccepted');const button=page.locator('button[type=submit]');
        assert.equal(await box.isChecked(),false);
        assert.equal(await button.isDisabled(),javaScriptEnabled);
        await page.locator('summary').click();
        assert.equal(await page.locator('details').getAttribute('open'),'');
        assert.equal(await page.locator('details script').count(),0);
        assert.equal(await page.evaluate(()=>window.noticeXss),undefined);
        if(!sso){await page.locator('#inputUsername').fill('fixture');await page.locator('#inputPassword').fill('FixturePassword123');}
        if(!javaScriptEnabled){
          await button.click();assert.equal(page.url(),url,'Unchecked form submitted without JavaScript');
        }
        await box.check();assert.equal(await button.isEnabled(),true);
        await box.uncheck();assert.equal(await button.isDisabled(),javaScriptEnabled);
        await box.check();
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,'Notice overflows mobile viewport');
        await Promise.all([page.waitForNavigation(),button.click()]);
        await page.waitForFunction(()=>document.body.textContent==='AUTHENTICATION_REACHED');
        assert.deepEqual(errors,[]);await page.close();
      }
      await context.close();
    }
    const editorContext=await browser.newContext();
    await editorContext.route('**/scripts/jquery.min.js',route=>route.fulfill({path:path.join(__dirname,'../adminportal/scripts/jquery.min.js'),contentType:'text/javascript'}));
    await editorContext.route('**/styles/bootstrap.min.css',route=>route.fulfill({path:path.join(__dirname,'../adminportal/styles/bootstrap.min.css'),contentType:'text/css'}));
    const editor=await editorContext.newPage();
    await editor.route('**/ajax/getmodule.php',route=>route.continue({url:base+'/login-notice-http-fixture.php?kind=admin&action=save'}));
    await editor.goto(base+'/login-notice-http-fixture.php?kind=admin&action=editor');
    const panel=editor.locator('#login-notice-editor');
    const summary=panel.locator('summary');
    for(const width of [1280,390]){
      await editor.setViewportSize({width,height:900});
      assert.equal(await panel.getAttribute('open'),null,'Editor should start collapsed');
      assert.equal(await panel.locator('textarea').isVisible(),false);
      assert.ok((await panel.boundingBox()).height<80,'Collapsed editor dominates the page');
      assert.equal(await editor.evaluate(()=>{
        const notice=document.getElementById('login-notice-editor');
        return ['updateGeneral','updateLdap','updateSaml','updateComplexity'].every(id=>
          !!(document.getElementById(id).compareDocumentPosition(notice)&Node.DOCUMENT_POSITION_FOLLOWING));
      }),true,'Notice displaced existing settings');
      assert.equal(await editor.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,'General settings overflow viewport');
    }
    await editor.setViewportSize({width:1280,height:1000});
    await summary.focus();await editor.keyboard.press('Enter');
    assert.equal(await panel.locator('textarea').isVisible(),true,'Keyboard cannot expand editor');
    const notice='Updated notice & café 👋 </textarea><script>window.noticeXss=true</script>';
    await editor.locator('textarea').fill(notice);
    await editor.locator('[data-notice-save]').click();
    await editor.waitForFunction(()=>document.querySelector('[data-notice-status]').textContent==='Login notice saved.');
    await editor.reload();
    assert.equal(await panel.getAttribute('open'),null);
    await summary.click();
    assert.equal(await panel.locator('[data-notice-state]').textContent(),'Enabled');
    assert.equal(await editor.locator('textarea').inputValue(),notice);
    assert.equal(await editor.evaluate(()=>window.noticeXss),undefined);
    await editor.locator('#login-notice-editor-enabled').uncheck();
    await editor.locator('[data-notice-save]').click();
    await editor.waitForFunction(()=>document.querySelector('[data-notice-status]').textContent==='Login notice saved.');
    assert.equal(await panel.locator('[data-notice-state]').textContent(),'Disabled');
    await editor.reload();
    assert.equal(await panel.locator('[data-notice-state]').textContent(),'Disabled');
    await summary.click();
    assert.equal(await editor.locator('#login-notice-editor-enabled').isChecked(),false);
    assert.equal(await editor.locator('textarea').inputValue(),notice);
    await editor.route('**/ajax/getmodule.php',route=>route.fulfill({status:200,contentType:'text/html',body:'<script>window.location="./"</script>'}));
    await editor.locator('[data-notice-save]').click();
    await editor.waitForFunction(()=>document.querySelector('[data-notice-status]').textContent==='Could not save the notice. Reload and try again.');
    await editorContext.close();
    console.log('PASS issue 105 browser: compact General-tab placement, keyboard expansion, settings save/reload/disable/expired-session, admin/sponsor/captive password and SSO flows, JavaScript on/off, native checkbox enforcement, expandable safe text and mobile layout');
  } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exit(1);});
