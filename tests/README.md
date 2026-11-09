Run the isolated PHP regressions and syntax checks:

```sh
docker run --rm --network none -v "$PWD:/app:ro" -w /app php:8.3-cli sh tests/run.sh
```

For actual SQL execution, build the test image and start a disposable database on an internal network. No application configuration is loaded. The integration test creates a randomly named database and removes it in a finally block.

```sh
docker build -t ipsk-review-php:local -f tests/Dockerfile .
docker network create --internal ipsk-review-test
docker run -d --rm --name ipsk-review-db --network ipsk-review-test -e MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1 mariadb:11.4
# Wait until the database reports ready before running:
docker run --rm --network ipsk-review-test -v "$PWD:/app:ro" -w /app ipsk-review-php:local php tests/database-integration.php
docker stop ipsk-review-db
docker network rm ipsk-review-test
```

The PHP suite also checks every deployed library copy against
`supportfiles/vendor-manifest.json` and exercises the real bundled PHPMailer
against a disposable loopback SMTP peer (authenticated and anonymous delivery,
rejected recipients, MIME alternatives, and unchanged passwords/PSKs). No mail
leaves the test container. The manifest records pinned download URLs and SHA-256
checksums; the DataTables bundle concatenates its listed upstream files in order.

Run the browser library regressions using a test-only Playwright installation:

```sh
npm install --prefix /tmp/ipsk-browser-tests --ignore-scripts --no-audit --no-fund playwright@1.65.0
/tmp/ipsk-browser-tests/node_modules/.bin/playwright install chromium
IPSK_PLAYWRIGHT_PATH=/tmp/ipsk-browser-tests/node_modules/playwright node tests/vendor-browser.cjs
```

Alternatively, set `IPSK_CHROME_PATH` to an installed Chrome executable. These
checks load the actual admin/sponsor/captive library copies and the application's
table/dashboard initializers into a synthetic local page. They cover pagination,
sorting, column filtering and visibility, saved state, responsive layout, Bootstrap
modals/dropdowns, menu keyboard navigation and Escape dismissal, icons, clipboard
copying, and all six dashboard charts. External
browser requests are blocked; no application database or credentials are needed.
Node, Playwright, and the browser are only test dependencies, not deployment
requirements. SMTP TLS and live ISE/LDAP integrations still need deployment checks.
