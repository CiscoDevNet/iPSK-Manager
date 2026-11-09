// Requires playwright or playwright-core; see tests/README.md. No live app needed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require(process.env.IPSK_PLAYWRIGHT_PATH || 'playwright');
const root = path.resolve(__dirname, '..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8').replace(/\r\n/g, '\n');
const columns = ['MAC', 'Name', 'Group', 'Email', 'Description', 'Owner', 'View', 'Actions'];
const rows = Array.from({length: 30}, (_, i) => `<tr>${[
  `AA:BB:CC:DD:EE:${String(i).padStart(2, '0')}`, `User ${String(i).padStart(2, '0')}`,
  'Test group', 'user@example.test', 'Test device', 'Test owner', 'View', 'Edit'
].map(v => `<td>${v}</td>`).join('')}</tr>`).join('');
const fixture = `<!doctype html><html><head><meta charset="utf-8"><title>Vendor regression</title></head><body>
<div class="container"><i data-feather="wifi"></i><input id="key" value="Preserve&amp;Key123"><button id="copy" data-clipboard-target="#key">Copy</button>
<div class="dropdown"><button id="dropdown" data-bs-toggle="dropdown">Menu</button><ul class="dropdown-menu"><li><button class="dropdown-item">Action</button></li></ul></div>
<div id="modal" class="modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body">Test</div></div></div></div>
<table id="endpoint-table" class="table"><thead><tr id="endpoint-table-header">${columns.map(c => `<th>${c}</th>`).join('')}</tr>
<tr id="endpoint-table-filter">${columns.map(() => '<th></th>').join('')}</tr></thead><tbody>${rows}</tbody></table>
${Array.from({length: 6}, (_, i) => `<div style="width:400px;height:180px"><canvas id="chart${i + 1}"></canvas></div>`).join('')}</div></body></html>`;

function tableInitializer(file) {
  const source = read(file);
  const start = source.indexOf('$(document).ready( function makeDataTable()');
  const end = source.indexOf('\n\t} );', start);
  assert(start >= 0 && end > start, `Cannot locate actual table initializer in ${file}`);
  return source.slice(start, end + '\n\t} );'.length);
}

(async () => {
  const browser = await chromium.launch({headless: true,
    ...(process.env.IPSK_CHROME_PATH ? {executablePath: process.env.IPSK_CHROME_PATH} : {})});
  try {
    const context = await browser.newContext({viewport: {width: 1280, height: 900}});
    // A synthetic origin enables real localStorage while preventing any external requests.
    await context.route('**/*', route => route.request().url().startsWith('http://ipsk.test/')
      ? route.fulfill({contentType: 'text/html', body: fixture}) : route.abort());
    for (const [index, bundle] of [
      ['adminportal', 'supportfiles/adminportals/modules/endpoints/endpoints.inc.php'],
      ['supportfiles/portals/sponsorportal/commonfiles', 'supportfiles/portals/sponsorportal/default/manage.inc.php'],
      ['supportfiles/portals/captiveportal/commonfiles', null]
    ].entries()) {
      const [dir, tableSource] = bundle;
      const page = await context.newPage();
      const errors = [];
      page.on('pageerror', error => errors.push(error.stack));
      page.on('console', message => { if (message.type() === 'error') errors.push(message.text() + ' ' + JSON.stringify(message.location())); });
      page.on('dialog', async dialog => { errors.push(dialog.message()); await dialog.dismiss(); });
      async function load() {
        await page.goto(`http://ipsk.test/vendor-${index}`);
        await page.addStyleTag({path: path.join(root, dir, 'styles/bootstrap.min.css')});
        for (const file of ['jquery.min.js', 'bootstrap.bundle.min.js', 'popper.min.js', 'feather.min.js', 'clipboard.min.js']) {
          await page.addScriptTag({path: path.join(root, dir, 'scripts', file)});
        }
        if (tableSource) {
          await page.addStyleTag({path: path.join(root, dir, 'styles/datatables.min.css')});
          await page.addScriptTag({path: path.join(root, dir, 'scripts/datatables.min.js')});
          await page.addScriptTag({path: path.join(root, dir, 'scripts/datatables-compat.js')});
          await page.evaluate(() => { $.fn.dataTable.ext.errMode = 'throw'; });
          await page.addScriptTag({content: tableInitializer(tableSource)});
          await page.waitForFunction(() => $.fn.dataTable.isDataTable('#endpoint-table'));
        }
      }
      await load();
      await page.evaluate(() => {
        feather.replace();
        window.clipboardTest = new ClipboardJS('#copy');
        clipboardTest.on('success', event => { window.copiedText = event.text; });
      });
      assert.equal(await page.locator('svg.feather-wifi').count(), 1);
      await page.locator('#copy').click();
      assert.equal(await page.evaluate(() => window.copiedText), 'Preserve&Key123');
      await page.locator('#dropdown').click();
      assert(await page.locator('.dropdown-menu').evaluate(el => el.classList.contains('show')));
      await page.keyboard.press('ArrowDown');
      assert(await page.locator('.dropdown > .dropdown-menu .dropdown-item').evaluate(el => el === document.activeElement));
      await page.keyboard.press('Escape');
      assert.equal(await page.locator('.dropdown > .dropdown-menu').isVisible(), false);
      await page.evaluate(() => new Promise(resolve => {
        const element = document.getElementById('modal');
        element.addEventListener('shown.bs.modal', resolve, {once: true});
        new bootstrap.Modal(element).show();
      }));
      assert(await page.locator('#modal').isVisible());
      await page.evaluate(() => new Promise(resolve => {
        const element = document.getElementById('modal');
        element.addEventListener('hidden.bs.modal', resolve, {once: true});
        bootstrap.Modal.getInstance(element).hide();
      }));
      if (tableSource) {
        assert.equal(await page.evaluate(() => $('#endpoint-table').DataTable().page.info().recordsTotal), 30);
        assert.equal(await page.locator('#endpoint-table tbody tr').count(), 15);
        await page.evaluate(() => $('#endpoint-table').DataTable().order([1, 'desc']).draw());
        assert.equal(await page.locator('#endpoint-table tbody tr').first().locator('td').nth(1).textContent(), 'User 29');
        await page.evaluate(() => $('#endpoint-table').DataTable().page('next').draw('page'));
        assert.equal(await page.evaluate(() => $('#endpoint-table').DataTable().page()), 1);
        await page.locator('#endpoint-table-filter input[placeholder="Filter Name"]').fill('User 07');
        await page.locator('#endpoint-table-filter input[placeholder="Filter Name"]').press('Enter');
        assert.equal(await page.evaluate(() => $('#endpoint-table').DataTable().rows({search: 'applied'}).count()), 1);
        await page.locator('.buttons-colvis').click();
        assert(await page.locator('.dt-button-collection').isVisible());
        await page.locator('.dt-button-collection .buttons-columnVisibility').filter({hasText: /^Group$/}).click();
        assert.equal(await page.evaluate(() => $('#endpoint-table').DataTable().column(2).visible()), false);
        const focused = await page.evaluate(() => document.activeElement.textContent);
        await page.keyboard.press('ArrowDown');
        assert.notEqual(await page.evaluate(() => document.activeElement.textContent), focused);
        await page.keyboard.press('ArrowUp');
        assert.equal(await page.evaluate(() => document.activeElement.textContent), focused);
        await page.keyboard.press('Escape');
        await page.waitForFunction(() => !document.querySelector('.dt-button-collection'));
        await page.evaluate(() => {
          const table = $('#endpoint-table').DataTable();
          table.column(2).visible(false);
          table.state.save();
        });
        await load();
        assert.equal(await page.evaluate(() => $('#endpoint-table').DataTable().rows({search: 'applied'}).count()), 1);
        assert.equal(await page.evaluate(() => $('#endpoint-table').DataTable().column(2).visible()), false);
        assert.equal(await page.locator('#endpoint-table-filter input[placeholder="Filter Name"]').inputValue(), 'User 07');
        await page.setViewportSize({width: 390, height: 844});
        await page.evaluate(() => $('#endpoint-table').DataTable().columns.adjust().responsive.recalc());
      }
      if (dir === 'adminportal') {
        await page.addScriptTag({path: path.join(root, 'adminportal/scripts/chart.min.js')});
        const source = read('supportfiles/adminportals/modules/dashboard/dashboard.inc.php');
        const chartScript = [...source.matchAll(/<script>([\s\S]*?)<\/script>/g)].find(m => m[1].includes('new Chart'));
        assert(chartScript, 'Dashboard chart script missing');
        await page.addScriptTag({content: chartScript[1].replace(/<\?php[\s\S]*?\?>/g, match => /Label/.test(match) ? "'One','Two'" : '1,2')});
        assert.equal(await page.evaluate(() => Object.keys(Chart.instances).length), 6);
        assert(await page.evaluate(() => Object.values(Chart.instances).every(chart => chart.width > 0 && chart.height > 0)));
      }
      assert.deepEqual(errors, [], `Browser errors for ${dir}`);
      await page.close();
      console.log(`PASS vendor browser: ${dir}`);
    }
    await context.close();
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
