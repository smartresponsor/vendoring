# Run Vendoring zero-controller checks
# Run from Vendoring repository root.

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

php tools/smoke/vendoring-zero-controller-audit.php
php tests/bin/vendoring-zero-controller-hardening-smoke.php
php tests/bin/vendor-route-map-coverage-smoke.php
php tests/bin/vendor-business-route-map-smoke.php
php tests/bin/vendor-attachment-route-map-smoke.php
php tests/bin/entrypoint-contract-smoke.php
php tests/bin/root-structure-smoke.php

php tests/bin/symfony-stack-smoke.php
php tests/bin/transaction-route-smoke.php
php tests/bin/vendor-active-docs-zero-controller-smoke.php
php tests/bin/vendor-runtime-service-contract-smoke.php
php tests/bin/vendor-runtime-artifact-inventory-smoke.php
php tests/bin/vendor-http-support-service-smoke.php
