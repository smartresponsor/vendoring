# RC operator surface

Vendoring now includes a minimal server-rendered operator surface for the vendor transaction contour.

## Scope

The operator surface is intentionally narrow:

- `GET /ops/vendor-transactions/{vendorId}` renders a Bootstrap-styled HTML page.
- `POST /ops/vendor-transactions/{vendorId}/create` submits a minimal create form.
- `POST /ops/vendor-transactions/{vendorId}/{id}/status` submits a minimal status-update form.

## What this proves for release-candidate hardening

This slice demonstrates a live server-rendered runtime chain that complements the JSON API runtime proofs:

- route resolution
- controller execution
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
