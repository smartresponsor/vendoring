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

## Operator contour hardening

The minimal operator/admin slice is now more route-scoped and less fragile:

- vendor identity is display-only in both Twig and fallback surfaces
- create submissions rely on the route `vendorId` instead of editable form scope
- Twig status rendering uses canonical human-readable labels instead of raw codes

This keeps the operator surface thin while reducing scope drift between URL, form payload, and displayed state.

## Payout orchestration hardening

The payout contour now distinguishes:

- tenant-aware payout creation input
- reserve recording with explicit `tenantId`
- real provider orchestration during `process()`
- deterministic `processed` and `failed` outcomes
- provider/account metadata persistence for later audit and runtime readback

This replaces the earlier demo-like success assumption with a more RC-oriented orchestration path.

## Statement hardening slice

The vendor statement contour now computes:

- opening balance from pre-period ledger movements
- period earnings
- period refunds
- period fees
- closing balance as `opening + earnings - refunds - fees`
- date-boundary normalization so datetime-like inputs still resolve to calendar-day statement windows

This keeps the statement surface closer to a finance/runtime read model rather than a flat demo export.

## Next target surface

The next RC-strengthening step is broader operator/runtime convergence and consumer-facing runtime views around the remaining vendor domains.

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
