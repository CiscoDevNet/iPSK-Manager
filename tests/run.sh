#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
for test in tests/endpoint-extension.php tests/review-[0-9]*.php tests/migration-definers.php tests/vendor-libraries.php; do
    output=$(php -d error_reporting=22527 -d log_errors=1 -d error_log=/dev/stderr "$test" 2>&1)
    printf '%s\n' "$output"
    if printf '%s\n' "$output" | grep -E 'PHP (Warning|Notice|Fatal error):'; then exit 1; fi
done
find adminportal portals supportfiles tests -name '*.php' -exec php -d error_reporting=22527 -l {} \; > /tmp/ipsk-php-lint.log
if grep -E 'Parse error|Errors parsing' /tmp/ipsk-php-lint.log; then exit 1; fi
printf 'PHP syntax checks passed\n'
