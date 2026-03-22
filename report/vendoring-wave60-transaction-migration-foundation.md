# Vendoring Wave 60 — Transaction Migration Foundation

## What was added
- Added PostgreSQL SQL migration for `vendor_transaction` under `migrations/MigrationPg/`.
- Added SQLite SQL migration for `vendor_transaction` under `migrations/MigrationSqlite/`.
- Added contract test coverage for both SQL migration files.
- Added `tests/bin/transaction-migration-smoke.php`.
- Extended `composer.json` with `test:transaction-migration` and included it in `quality`.

## Why this wave exists
Wave 59 established Doctrine ORM attributes for `VendorTransaction`, but the repository still lacked an explicit schema foundation. This wave adds dialect-specific SQL migrations aligned with the repository canon so the transaction persistence surface is no longer ORM-only.

## Verified in container
- PHP lint for new test and smoke files.
- `php tests/bin/transaction-migration-smoke.php`
- `php tests/bin/smoke.php`
