# Vendoring RC Runtime Surfaces

## Current strongest surface

The strongest current runtime contour is `VendorTransaction`.
It now spans:

- Cruding-owned URI grammar
- canonical `App\Vendoring\Service\Http\Vendor\...` runtime services
- manager/service orchestration
- amount and status policy checks
- repository abstractions
- Doctrine-backed persistence tests
- SQLite integration evidence
- JSON/error/idempotency contract checks

## Additional proven surfaces

- statement generation and delivery
- payout calculation and orchestration
- repository and DI alias coverage
- Symfony mini-stack smoke
- route-map registry coverage
- FQCN service/type coverage

## Next target surface

The next RC-strengthening step is a minimal operator/admin path that traverses:

- route-map key
- Cruding parser
- canonical HTTP service
- Symfony form type where needed
- validation
- service/repository
- Twig rendering when needed
- Bootstrap-based markup when a UI surface is present

That operator surface should be thin and evidence-oriented rather than a full product UI.

## Runtime evidence

The release-candidate runtime surface includes a kernel-handled transaction vertical slice that proves:

- POST `/api/vendor/transaction` creates a persisted transaction
- GET `/api/vendor/transaction/{vendorId}` reads persisted transactions from a fresh database
- POST `/api/vendor/transaction/status/{vendorId}/{id}` updates persisted state through the real Symfony runtime
- a fresh SQLite-backed boot can start in `prod` mode and serve an empty transaction collection

These proofs are covered by runtime and smoke tests under `tests/Integration/Runtime/` and `tests/bin/`.
