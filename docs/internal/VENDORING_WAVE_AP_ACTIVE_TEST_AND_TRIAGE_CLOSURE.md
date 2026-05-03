# Vendoring Wave AP — Active test and triage closure

## Purpose

Close the remaining active naming residue after Wave AO without touching production `src/` behavior.

## Changes

- Renamed the active service/entity compatibility test from `VendorEntityServiceCompatibilityTest` to `VendorServiceEntityCompatibilityTest`.
- Kept `VendorEntity` as a valid PHP/Doctrine class name where it is used as an entity type.
- Updated `tools/vendoring-missing-class-triage.php` wording so an empty scan no longer recommends quarantining restored entity classes.
- Preserved the missing-class triage role as a structural guard for future waves.

## Validation

- `php -l` passes for changed PHP files.
- `php tools/vendoring-missing-class-triage.php --limit=2000` reports zero issues.
- `node tools/canon/vendor-canon-scan.mjs` reports OK.
- `php tools/canon/vendor-scan.php` reports OK.
