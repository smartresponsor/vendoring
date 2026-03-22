# Vendoring Wave 81 - Root Runtime Cache Cleanup

## Scope
- removed persistent runtime cache artifact from `var/`
- added contract/smoke guards preventing committed `.php-cs-fixer.cache` in cumulative snapshots
- extended composer quality pipeline with root runtime artifact enforcement

## Files changed
- removed `var/.php-cs-fixer.cache`
- added `tests/Unit/Infrastructure/CanonicalRootRuntimeArtifactContractTest.php`
- added `tests/bin/root-runtime-artifact-smoke.php`
- updated `tests/bin/smoke.php`
- updated `composer.json`

## Validation
- `php -l tests/Unit/Infrastructure/CanonicalRootRuntimeArtifactContractTest.php`
- `php -l tests/bin/root-runtime-artifact-smoke.php`
- `php -l tests/bin/smoke.php`
- `php tests/bin/root-runtime-artifact-smoke.php`
- `php tests/bin/smoke.php`
