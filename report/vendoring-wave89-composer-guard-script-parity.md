# Vendoring Wave 89 — Composer guard script parity

## Summary
This wave normalizes the composer orchestration for `no-stub` and `no-placeholder` guards so they follow the same executable pattern used by the stronger canonical guard slices: a dedicated smoke script plus a PHPUnit unit-filter invocation.

## Changes
- normalized composer scripts:
  - `test:no-stub-config`
  - `test:no-placeholder-source`
  - `test:no-stub-source`
  - `test:no-placeholder-repository`
- added `test:composer-guard-parity`
- added `tests/Unit/Infrastructure/ComposerGuardScriptParityTest.php`
- added `tests/bin/composer-guard-parity-smoke.php`
- updated `tests/bin/smoke.php`
- extended `quality` with `@test:composer-guard-parity`

## Why
The active cumulative slice still had these guard families wired inconsistently. Some used direct PHPUnit file execution while other canonical guard slices already used the stronger and more uniform `--testsuite unit --filter ...` pattern. This wave makes that orchestration surface explicit and self-consistent.
