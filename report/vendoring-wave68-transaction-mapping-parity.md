# Vendoring Wave 68 — Transaction Mapping Parity

## Scope
- Fix ORM/SQL parity drift in `VendorTransaction` mapping.
- Repair missing Composer script coverage for transaction status persistence.
- Add mapping-focused smoke and unit checks.

## Changes
- Added explicit Doctrine column names for `status` and `created_at` in `VendorTransaction`.
- Added `test:transaction-status-persistence` to `composer.json` because `quality` already referenced it implicitly.
- Added `test:transaction-mapping` to validate ORM/SQL column-name parity.
- Added `tests/Unit/Infrastructure/VendorTransactionMappingParityTest.php`.
- Added `tests/bin/transaction-mapping-parity-smoke.php`.

## Why this matters
Before this wave, the transaction SQL migration used snake_case columns like `created_at`, while the entity mapping left `createdAt` implicit. That made ORM/schema parity depend on external naming strategy assumptions.

This wave makes the mapping explicit and keeps Composer quality scripts internally consistent.
