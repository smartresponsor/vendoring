# Vendoring Wave 85 — Root operational log cleanup

## Scope
- remove committed operational log artifact from cumulative snapshot
- extend runtime-artifact guard to cover `.commanding/logs/actions.log`
- keep source snapshot clean from runtime-generated logs

## Changes
- removed `.commanding/logs/actions.log`
- extended `CanonicalRootRuntimeArtifactContractTest`
- extended `tests/bin/root-runtime-artifact-smoke.php`
- ensured `composer.json` exposes `test:root-runtime-artifacts`

## Verification
- php lint on changed PHP files
- `php tests/bin/root-runtime-artifact-smoke.php`
- `php tests/bin/smoke.php`
