# Vendoring Wave 98: IDEA XML Artifact Cleanup

- Removed committed IDE XML runtime artifacts under `.idea/`.
- Extended IDEA runtime artifact guard to catch `.idea/*.xml` and nested `.idea/**/*.xml`.
- Kept `.idea/` dot-folder allowed, while excluding machine-local XML state from cumulative snapshots.
