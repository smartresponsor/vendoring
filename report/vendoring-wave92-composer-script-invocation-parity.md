# Vendoring Wave 92 — Composer Script Invocation Parity

## Summary
- normalized PHPUnit script invocations to `php vendor/bin/phpunit ...`
- removed duplicate `@test:transaction-status-persistence` entry from `quality`
- added guard coverage for composer script invocation parity

## Files
- `composer.json`
- `tests/Unit/Infrastructure/ComposerScriptInvocationParityTest.php`
- `tests/bin/composer-script-invocation-parity-smoke.php`
- `tests/bin/smoke.php`
