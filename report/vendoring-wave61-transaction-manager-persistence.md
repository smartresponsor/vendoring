# Vendoring Wave 61 — Transaction Manager & Persistence Slice

## Scope
This wave strengthens the real execution path around `VendorTransactionManager` and the transaction persistence surface introduced by the migration foundation.

## Changes
- Added `tests/Unit/Transaction/VendorTransactionManagerTest.php`
  - verifies `createTransaction()` persists, flushes, and dispatches `VendorTransactionEvent`
  - verifies `updateStatus()` normalizes status, flushes, and dispatches on valid transitions
  - verifies invalid transitions fail before `flush()` and before event dispatch
- Added `tests/bin/transaction-persistence-smoke.php`
  - validates the SQLite `vendor_transaction` migration contract
  - validates newest-first repository ordering contract
  - executes an in-memory SQLite persistence proof when `pdo_sqlite` is available
  - falls back to static contract validation when the extension is not available
- Updated `composer.json`
  - added `test:transaction-persistence`
  - expanded `quality` to include the new slice
- Updated `tests/bin/smoke.php`
  - now requires the transaction persistence smoke script

## Why this wave matters
Previous waves established entity mapping, repository contracts, status policy, and migration files. This wave adds an executable guard for the transaction manager behavior and a persistence-oriented smoke layer around the transaction table contract.
