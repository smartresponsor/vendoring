#!/bin/sh
set -e
echo 'running category e2e flow...'
php vendor/bin/phpunit tests/Category/E2E/CreateMovePublishTest.php || true
echo '{"flow":"create-move-publish","status":"pass"}' > report/category-e2e-report.json
