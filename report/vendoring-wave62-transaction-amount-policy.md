# Vendoring Wave 62 — Transaction Amount Policy

## Scope
- strengthen transaction create flow with explicit amount normalization and validation
- reject non-numeric, zero, and negative amounts before persistence
- harden SQL schema with positive-amount CHECK constraints

## Changes
- added `VendorTransactionAmountPolicyInterface`
- added `VendorTransactionAmountPolicy`
- wired `VendorTransactionManager` to normalize amount before entity creation
- mapped create-flow invalid input to HTTP `422` in `VendorTransactionController`
- added PostgreSQL and SQLite `CHECK (amount > 0)` for `vendor_transaction.amount`
- added unit tests and smoke coverage for amount policy and create-flow guard

## Outcome
- transaction create flow no longer accepts arbitrary or non-positive amount strings
- persistence foundation now reflects the same positive-amount invariant at SQL level
