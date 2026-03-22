# Vendoring wave 73 — transaction JSON controller consistency

## Scope
Closed the real controller drift present in the active wave72 cumulative slice.

## Fixes
- Removed redundant nested malformed-JSON handling from `create()` in `VendorTransactionController`.
- Added missing bounded malformed-JSON handling to `updateStatus()`.
- Strengthened `tests/bin/transaction-json-surface-smoke.php` so it now requires malformed-JSON coverage in both transaction controller actions.

## Verified
- `php -l src/Controller/VendorTransactionController.php`
- `php -l tests/bin/transaction-json-surface-smoke.php`
- `php tests/bin/transaction-json-surface-smoke.php`
- `php tests/bin/smoke.php`
