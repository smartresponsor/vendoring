# Vendoring Wave 126 — Transaction SQLite integration foundation

## Scope
Add a real Doctrine SQLite integration slice for `VendorTransaction` so the transaction flow is exercised against a live in-memory database schema instead of only mocks and static smoke contracts.

## Delivered
- `tests/Integration/Transaction/VendorTransactionSqliteIntegrationTest.php`
- `tests/Support/Transaction/DoctrineEntityManagerFactory.php`
- `tests/Support/Transaction/DoctrineBackedVendorTransactionRepository.php`
- `tests/bin/transaction-sqlite-integration-smoke.php`
- `composer.json` updated with `test:transaction-sqlite-integration`
- `quality` includes `@test:transaction-sqlite-integration`
- `tests/bin/smoke.php` now requires the sqlite integration smoke/script

## Verified here
- PHP lint on new PHP files
- `tests/bin/transaction-sqlite-integration-smoke.php`
- `tests/bin/smoke.php`

## Note
This environment validates the integration slice structurally. The real PHPUnit+Doctrine runtime execution depends on installed Composer dependencies and `pdo_sqlite` availability in the target environment.
