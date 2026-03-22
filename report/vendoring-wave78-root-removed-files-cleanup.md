# Vendoring Wave 78 — Root Removed-Files Cleanup

## Summary
- removed persistent root-level `REMOVED_FILES.txt` from the cumulative snapshot
- added contract and smoke guards to prevent `REMOVED_FILES.txt` from remaining in repository root
- extended Composer quality pipeline with `test:root-removed-files`

## Why
`REMOVED_FILES.txt` is acceptable as a transient touched-delivery aid, but it should not persist as a root-level artifact inside the cumulative repository snapshot.
