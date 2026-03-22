# Vendoring wave24 report

## Scope
- Active base: cumulative snapshot wave23
- Focus: live statement service semantic mismatch in `src/Service/Vendor/Statement`

## Change
- Fixed `VendorStatementService::build()` so ledger totals now respect the statement DTO scope:
  - `tenantId`
  - `vendorId`
  - `from`
  - `to`
  - `currency`

## Why
Previously statement totals were computed tenant-wide and ignored vendor/time/currency scope carried by `VendorStatementRequestDTO`. That produced semantic drift between the public statement request contract and the actual ledger aggregation.

## Validation
- `php -l src/Service/Vendor/Statement/VendorStatementService.php`
- `php tools/vendoring-structure-scan.php --strict`
- `php tools/vendoring-psr4-scan.php --strict`
- `php tools/vendoring-missing-class-scan-v3.php --strict --limit=500`
- `php tools/vendoring-quality-gate-v3.php`
