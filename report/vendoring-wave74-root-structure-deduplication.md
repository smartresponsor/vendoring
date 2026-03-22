# Vendoring Wave 74 – Root Structure Deduplication

## Summary
- Removed forbidden root-level duplicate `VendorTransactionController.php`.
- Preserved canonical controller only at `src/Controller/VendorTransactionController.php`.
- Added root-structure contract test and smoke guard.
- Registered `test:root-structure` in `composer.json` quality surface.

## Rationale
The active slice contained two `VendorTransactionController.php` files: one canonical under `src/Controller/` and one forbidden duplicate at repository root. This violated the Symfony-oriented single-root application canon and created ambiguity for tooling and scans.

## Files Changed
- deleted `VendorTransactionController.php`
- added `tests/Unit/Infrastructure/CanonicalRootStructureContractTest.php`
- added `tests/bin/root-structure-smoke.php`
- updated `tests/bin/smoke.php`
- updated `composer.json`
- added `report/vendoring-wave74-root-structure-deduplication.md`
