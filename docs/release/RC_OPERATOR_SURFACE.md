# RC operator surface

Vendoring now includes a minimal server-rendered operator surface for the vendor transaction contour.

## Scope

The operator surface is intentionally narrow:

- `GET /ops/vendor/transaction/{vendorId}` renders a Bootstrap-styled HTML page.
- `POST /ops/vendor/transaction/create/{vendorId}` submits a minimal create form.
- `POST /ops/vendor/transaction/status/{id}` submits a minimal status-update form.

## Authorization and ownership

All transaction operator routes require an explicit bearer API key for the active canonical vendor. GET requires read:transactions; POST requires write:transactions. The canonical vendor identifier is the persisted integer ID encoded as a string, not a tenant-local ledger identifier. Both list and status lookup require the persisted owner association. Historical rows without an independently verified owner are not exposed or mutable. The migration does not guess owners. Vendor deletion is restricted while owned transactions remain.

Native browser navigation and form submission require a trusted host authentication integration that supplies the bearer header for the same origin. Do not put API keys in query strings, cookies or form fields. Ordinary unauthenticated navigation is intentionally denied. The isolated browser acceptance uses ephemeral fixture credentials.

## What this proves for release-candidate hardening

This slice demonstrates a live server-rendered runtime chain that complements the JSON API runtime proofs:

- route resolution
- surface builder execution
- HTML response rendering
- request form handling
- service/policy execution
- Doctrine persistence
- read-after-write operator feedback

## Current implementation choice

This wave keeps the implementation dependency-light and uses Symfony HTTP handling plus handcrafted Bootstrap markup.
It is a stepping stone toward a later Twig/Form/Nelmio/OpenAPI documentation wave without widening the component scope prematurely.

## Test evidence

- `tests/Integration/Runtime/VendorTransactionOperatorSurfaceTest.php`
- `tests/bin/operator-surface-smoke.php`
