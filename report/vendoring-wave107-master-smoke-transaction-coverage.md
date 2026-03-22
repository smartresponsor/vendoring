# Vendoring Wave 107 — Master smoke transaction coverage

## What changed
- Expanded `tests/bin/smoke.php` so master smoke now requires the full transaction script set, not only a partial subset.
- Added explicit composer-script presence checks for:
  - `test:transaction-policy`
  - `test:transaction-amount`
  - `test:transaction-doctrine`
  - `test:transaction-migration`
  - `test:transaction-persistence`
  - `test:transaction-idempotency`
  - `test:transaction-error-surface`
  - `test:transaction-json`
- Added explicit smoke-file existence checks for:
  - `tests/bin/transaction-error-surface-smoke.php`
  - `tests/bin/transaction-json-surface-smoke.php`

## Why
Active transaction guards already existed in the repository and were wired into `quality`, but master smoke still validated only a narrower transaction subset. This wave closes that orchestration gap so the top-level smoke surface matches the actual transaction guard family.

## Verification
- `php -l tests/bin/smoke.php`
- `php tests/bin/smoke.php`
