# Vendoring Wave 94 — No Stub Repository Cleanup

## Summary
- removed remaining `stubs` wording from `.deploy/MANIFEST.md`
- removed legacy `Stub` test-segment marker from `.commanding/reorganize-tests.ps1`
- added repository-level no-stub contract test
- added repository-level no-stub smoke guard
- registered `test:no-stub-repository` in composer orchestration
- extended master smoke and quality orchestration to include the new guard
