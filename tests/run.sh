#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
for test in tests/endpoint-extension.php tests/review-[0-9]*.php; do
    php -d error_reporting=22527 "$test"
done
find adminportal portals supportfiles tests -name '*.php' -not -path '*/phpmailer/*' -exec php -d error_reporting=22527 -l {} \; > /tmp/ipsk-php-lint.log
if grep -E 'Parse error|Errors parsing' /tmp/ipsk-php-lint.log; then exit 1; fi
printf 'PHP syntax checks passed\n'
