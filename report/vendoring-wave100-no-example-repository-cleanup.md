# Vendoring Wave 100 - No Example Repository Cleanup

## Summary
- removed `example.com` markers from `.commanding` notification test scripts
- added repository-level guard for `example.com` in operational layers
- wired the guard into composer and master smoke orchestration

## Files changed
- `.commanding/deploy/notificationTest.sh`
- `.commanding/test/notificationTest.sh`
- `tests/Unit/Infrastructure/CanonicalNoExampleRepositoryContractTest.php`
- `tests/bin/no-example-repository-smoke.php`
- `tests/bin/smoke.php`
- `composer.json`
- `report/vendoring-wave100-no-example-repository-cleanup.md`
