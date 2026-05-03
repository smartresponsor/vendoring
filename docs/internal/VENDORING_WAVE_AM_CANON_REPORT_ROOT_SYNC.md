# Vendoring Wave AM — canon report/root-path sync

## Scope

This wave keeps the post-cleanup repository checks aligned with the canonical repository layout after the root cleanup waves.

## Changes

- Moved canon tool report output from `.report/` to `build/reports/canon/`.
- Updated canon tool README output paths.
- Updated repository smoke/contract scans from retired `.deploy`, `.smoke`, and `.release` paths to current `deploy/`, `ops/policy/smoke`, and `build/release` paths.
- Updated stale form canon wording from `Vendor*Type` to `Vendor*Form`.
- Replaced user-facing `VendorEntity Profile` fixture wording in active controller tests with `Vendor Profile`.

## Validation

- `php -l` passed for all changed PHP files.
- `node tools/canon/vendor-canon-scan.mjs` passed.
- `node tools/canon/migration-dialect-guard.mjs` passed.
- `php tools/canon/vendor-scan.php` passed.

## Notes

No production source classes were changed in this wave.
