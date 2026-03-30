# Vendoring RC Runtime Surfaces

## Current strongest surface

The strongest current runtime contour is `VendorTransaction`.
It already spans:

- HTTP/controller handling
- manager/service orchestration
- amount and status policy checks
- repository abstractions
- Doctrine-backed persistence tests
- SQLite integration evidence
- JSON/error/idempotency contract checks
- duplicate transaction normalization across both pre-check and DB-level unique violations

## Additional proven surfaces

- statement generation and delivery
- payout calculation and orchestration
- repository and DI alias coverage
- Symfony mini-stack smoke
- minimal operator status controls aligned to the canonical transaction vocabulary

## Next target surface

The next RC-strengthening step is a minimal operator/admin path that traverses:

- route
- controller
- Symfony form
- validation
- service/repository
- Twig rendering
- Bootstrap-based markup

That operator surface should be thin and evidence-oriented rather than a full product UI.


## Wave 03 runtime evidence

The release-candidate runtime surface now includes a kernel-handled transaction vertical slice that proves:

- POST `/api/vendor-transactions` creates a persisted transaction
- GET `/api/vendor-transactions/vendor/{vendorId}` reads persisted transactions from a fresh database
- POST `/api/vendor-transactions/vendor/{vendorId}/{id}/status` updates persisted state through the real Symfony runtime
- a fresh SQLite-backed boot can start in `prod` mode and serve an empty transaction collection

These proofs are covered by:

- `tests/Integration/Runtime/VendorTransactionKernelRuntimeTest.php`
- `tests/Integration/Runtime/ProductionKernelBootTest.php`
- `tests/bin/transaction-kernel-runtime-smoke.php`
- `tests/bin/fresh-db-boot-smoke.php`
