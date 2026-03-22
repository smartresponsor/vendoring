# Vendoring Wave 87 — No Placeholder Repository Cleanup

## Summary
- removed a remaining `placeholder` marker from `.deploy/_template/MANIFEST.md`
- added repository-level guard against `placeholder` markers outside `tests/` and `report/`
- extended Composer and master smoke orchestration for the new guard

## Files
- `.deploy/_template/MANIFEST.md`
- `tests/Unit/Infrastructure/CanonicalNoPlaceholderRepositoryContractTest.php`
- `tests/bin/no-placeholder-repository-smoke.php`
- `tests/bin/smoke.php`
- `composer.json`
