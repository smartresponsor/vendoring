# Vendoring Wave 80 — Root Removed-Files Enforcement

## Summary
This wave closes a factual drift left in the active cumulative slice: `REMOVED_FILES.txt` still persisted in the repository root, even though it should exist only as a touched-delivery helper and not as a permanent cumulative artifact.

## Changes
- removed root-level `REMOVED_FILES.txt` from the cumulative snapshot
- reinforced `tests/Unit/Infrastructure/CanonicalRootRemovedFilesContractTest.php`
- reinforced `tests/bin/root-removed-files-smoke.php`
- registered `test:root-removed-files` in `composer.json`
- extended `tests/bin/smoke.php` to require the new guard

## Validation
- `php -l` on changed PHP files
- `php tests/bin/root-removed-files-smoke.php`
- `php tests/bin/smoke.php`
