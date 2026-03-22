# Vendoring Wave 83 - No Placeholder Source Cleanup

## Summary
- removed placeholder markers from production source files
- added source-level contract test against placeholder markers under `src/`
- added smoke guard and composer script for the new contract

## Files changed
- `src/Service/CrmService.php`
- `src/Service/Statement/StatementExporterPDF.php`
- `tests/Unit/Infrastructure/CanonicalNoPlaceholderSourceContractTest.php`
- `tests/bin/no-placeholder-source-smoke.php`
- `tests/bin/smoke.php`
- `composer.json`
