# Vendoring wave 99 — root removed-files actualization

## What changed
- physically removed `REMOVED_FILES.txt` from the repository root of the cumulative snapshot
- preserved the existing guard layer:
  - `tests/Unit/Infrastructure/CanonicalRootRemovedFilesContractTest.php`
  - `tests/bin/root-removed-files-smoke.php`
  - `composer` script `test:root-removed-files`

## Why
The active cumulative slice still contained `REMOVED_FILES.txt` in the repository root even though prior guard layers already declared that file forbidden for cumulative snapshots. This wave closes the mismatch between declared contract and actual repository state.
