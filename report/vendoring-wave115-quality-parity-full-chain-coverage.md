# Vendoring Wave 115 — quality parity full-chain coverage

## Summary
Expanded the quality parity guard so it now verifies the full active `quality` chain, not just the transaction/root/repository tail.

## Changes
- extended `tests/Unit/Infrastructure/ComposerQualityScriptParityTest.php`
- extended `tests/bin/composer-quality-parity-smoke.php`
- parity now requires base chain entries such as `@test:symfony-stack`, `@test:di`, `@test:entrypoint`, `@lint:php`, `@phpstan`, `@test:mail`, `@test:statement-command`, `@test:repository`, `@test:unit`, `@test:controller`, `@test:entity`, `@test:compat`

## Validation
- `php -l tests/Unit/Infrastructure/ComposerQualityScriptParityTest.php`
- `php -l tests/bin/composer-quality-parity-smoke.php`
- `php tests/bin/composer-quality-parity-smoke.php`
- `php tests/bin/smoke.php`
