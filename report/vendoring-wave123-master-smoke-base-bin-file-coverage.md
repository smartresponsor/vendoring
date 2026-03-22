# Vendoring Wave 123 — Master Smoke Base Bin File Coverage

## Summary
Expanded `tests/bin/smoke.php` so master smoke now explicitly requires the physical presence of the remaining base smoke scripts that were still only covered indirectly through Composer scripts.

## Added file-presence assertions
- `tests/bin/symfony-stack-smoke.php`
- `tests/bin/di-smoke.php`
- `tests/bin/entity-smoke.php`
- `tests/bin/root-runtime-artifact-smoke.php`
- `tests/bin/transaction-doctrine-smoke.php`
- `tests/bin/transaction-status-persistence-smoke.php`

## Result
Master smoke now validates both dimensions for these base slices:
- smoke file presence
- paired composer script presence
