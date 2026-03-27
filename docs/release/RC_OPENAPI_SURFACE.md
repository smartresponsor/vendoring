# Vendoring RC OpenAPI Surface

The repository now ships a generated OpenAPI artifact for the strongest public HTTP contour.

## Current generated artifacts

- `build/docs/openapi.json`
- `build/docs/openapi.yaml`

## Covered surfaces

- `POST /api/vendor-transactions`
- `GET /api/vendor-transactions/vendor/{vendorId}`
- `POST /api/vendor-transactions/vendor/{vendorId}/{id}/status`
- `GET /ops/vendor-transactions/{vendorId}`

## Why this matters for RC

These artifacts turn the transaction/runtime contour into an inspectable contract that CI can publish.
They are intentionally repository-owned so release evidence does not depend on a later package installation step.

## Nelmio integration seam

The repository also includes disabled integration scaffolding:

- `config/packages/vendor_nelmio_api_doc.yaml.dist`
- `config/routes/vendor_nelmio_api_doc.yaml.dist`

Those files show the intended Symfony-native browsing surface without forcing the bundle into the active runtime before dependency installation is aligned.
