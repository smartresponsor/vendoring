# Vendoring Wave 64 - Null Project Idempotency

## Scope
- Closed the factual gap where wave63 reported duplicate/idempotency behavior that was not actually present in `VendorTransactionManager`.
- Normalized `projectId` blank-string input to `null` before duplicate lookup and persistence.
- Reworked SQL uniqueness semantics for nullable `project_id` using separate partial unique indexes for `NULL` and non-`NULL` cases.

## Changed files
- `src/Repository/VendorTransactionRepository.php`
- `src/Entity/Vendor/VendorTransaction.php`
- `src/Service/VendorTransactionManager.php`
- `src/Controller/VendorTransactionController.php`
- `tests/Support/Transaction/InMemoryVendorTransactionRepository.php`
- `tests/Support/Transaction/FakeVendorTransactionManager.php`
- `tests/Unit/Transaction/VendorTransactionManagerTest.php`
- `tests/Unit/Controller/VendorTransactionControllerTest.php`
- `tests/Unit/Infrastructure/TransactionMigrationContractTest.php`
- `tests/Unit/Repository/VendorTransactionRepositoryNullProjectContractTest.php`
- `tests/bin/transaction-idempotency-smoke.php`
- `migrations/MigrationPg/20260321_000001_create_vendor_transaction.sql`
- `migrations/MigrationSqlite/20260321_000001_create_vendor_transaction.sql`
- `composer.json`

## Result
- `duplicate_transaction` is now actually enforced in the manager before persist/flush.
- `projectId = NULL` is treated canonically and consistently across controller, manager, repository, and SQL schema.
- Test/support surface now matches the interface contract instead of drifting from it.
