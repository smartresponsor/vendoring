# Vendoring Wave 86 Report

## Scope
Guard parity enforcement for no-stub and no-placeholder source/config slices.

## Changes
- added missing Composer script `test:no-stub-source`
- extended `composer quality` to execute `@test:no-stub-source`
- strengthened `tests/bin/smoke.php` to require:
  - `tests/bin/no-stub-config-smoke.php`
  - `tests/bin/no-placeholder-source-smoke.php`
  - `tests/bin/no-stub-source-smoke.php`
- strengthened `tests/bin/smoke.php` to require Composer scripts:
  - `test:no-stub-config`
  - `test:no-placeholder-source`
  - `test:no-stub-source`

## Why
The guard files from recent waves existed physically, but the active slice still had a contract gap:
- `test:no-stub-source` was not registered in Composer
- the master smoke script did not validate the full guard trio

This wave makes the guard surface explicit and self-consistent.
