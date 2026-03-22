# Vendoring Wave 39 Report

## Scope
- Live command-layer boundary output normalization for vendor API key creation flow.

## Changed files
- `src/Command/Vendor/VendorApiKeyCreateCommand.php`
- `report/vendoring-wave39-report.md`

## What was fixed
- `VendorApiKeyCreateCommand` accepted `vendorId` and `permissions`, but command output returned only the raw token.
- The command output now preserves the accepted request scope together with the generated token.

## Result
- Console output is more audit/debug friendly and no longer loses vendor/request context at the boundary.
