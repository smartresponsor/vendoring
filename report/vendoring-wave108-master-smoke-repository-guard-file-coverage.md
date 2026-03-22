# Vendoring Wave 108 - Master Smoke Repository Guard File Coverage

## Summary
Extended `tests/bin/smoke.php` so master smoke explicitly requires the smoke files for repository/config guard slices that it already expected as composer scripts.

## Changes
- Added explicit file checks for:
  - `tests/bin/no-example-config-smoke.php`
  - `tests/bin/no-example-repository-smoke.php`
  - `tests/bin/no-example-wording-repository-smoke.php`
  - `tests/bin/app-namespace-repository-smoke.php`
  - `tests/bin/idea-module-artifact-smoke.php`
  - `tests/bin/no-stub-repository-smoke.php`
- Kept existing composer script checks intact.
- Updated success wording to reflect both smoke-file and script coverage.

## Validation
- `php -l tests/bin/smoke.php`
- `php tests/bin/smoke.php`
