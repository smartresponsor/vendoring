# Vendoring Wave 21 Report

Active base: cumulative snapshot wave19.

## Scope
- `src/Repository/Vendor/Ledger/LedgerEntryRepository.php`

## Findings
- The live repository implementation was structurally corrupted.
- `balancesForVendor()` had been duplicated multiple times and spliced into the constructor, `insert()`, `listByRef()`, and `sumByAccount()` bodies.
- This was a real runtime-shape defect inside the live Vendor repository layer, not a documentation or cleanup artifact.

## Changes
- Rebuilt `LedgerEntryRepository` as a coherent canonical implementation matching `LedgerEntryRepositoryInterface`.
- Restored four methods as a single valid class body:
  - `insert()`
  - `listByRef()`
  - `sumByAccount()`
  - `balancesForVendor()`
- Preserved the existing repository contract and current data shape.

## Verification
- `php -l src/Repository/Vendor/Ledger/LedgerEntryRepository.php` → PASS
- `php tools/vendoring-structure-scan.php --strict` → PASS
- `php tools/vendoring-psr4-scan.php --strict` → PASS
- `php tools/vendoring-missing-class-scan-v3.php --strict --limit=500` → PASS
- `php tools/vendoring-quality-gate-v3.php` → PASS

## Delivery
- touched-files archive
- cumulative snapshot archive
