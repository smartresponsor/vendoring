# Vendoring Wave 63 — Transaction Idempotency Foundation

## Scope
- add vendor/order/project duplicate guard to transaction create-flow
- declare unique persistence invariant in entity and SQL migrations
- cover repository contract, manager behavior, controller response mapping, and smoke scripts

## Changes
- `VendorTransactionRepositoryInterface` now exposes `existsForVendorOrderProject()`
- `VendorTransactionRepository` implements duplicate lookup
- `VendorTransactionManager` rejects duplicates before persist/flush with `duplicate_transaction`
- `VendorTransactionController` maps duplicate create attempts to `409`
- `VendorTransaction` now declares unique constraint on `(vendor_id, order_id, project_id)`
- PostgreSQL and SQLite migrations now create unique index `uniq_vendor_transaction_vendor_order_project`

## Test Surface
- extended `VendorTransactionManagerTest`
- extended `VendorTransactionControllerTest`
- extended `TransactionMigrationContractTest`
- added `tests/bin/transaction-idempotency-smoke.php`

## Outcome
Transaction create-flow now has an explicit idempotency/uniqueness invariant instead of allowing duplicate rows for the same vendor/order/project tuple.
