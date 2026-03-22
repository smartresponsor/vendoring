# Vendoring wave33 report

## Scope
- Active base: cumulative snapshot wave32
- Focus: live Vendor statement flow payload integrity

## Change
- Extended `VendorStatementService::build()` payload to preserve the request scope already accepted via `VendorStatementRequestDTO`:
  - `tenantId`
  - `vendorId`
  - `from`
  - `to`
  - `currency`

## Why
- Statement build logic already computes totals in vendor/time/currency scope.
- Before this wave, the returned payload dropped that scope and exposed only totals/items.
- This was a live payload-loss mismatch in the statement flow.

## Validation
- `php -l src/Service/Vendor/Statement/VendorStatementService.php`
- `php tools/vendoring-structure-scan.php --strict`
- `php tools/vendoring-psr4-scan.php --strict`
- `php tools/vendoring-missing-class-scan-v3.php --strict --limit=500`
- `php tools/vendoring-quality-gate-v3.php`
