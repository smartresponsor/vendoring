# Vendoring Wave 82 - No Stub Config Cleanup

## What changed
- Replaced forbidden `stub` providers in `ops/policy/config/crm.yaml` and `ops/policy/config/kms.yaml` with explicit `disabled` providers.
- Added contract test `tests/Unit/Infrastructure/CanonicalNoStubConfigContractTest.php`.
- Added smoke script `tests/bin/no-stub-config-smoke.php`.
- Extended Composer scripts with `test:no-stub-config` and included it in `quality`.

## Why
The active cumulative slice still contained explicit `stub` providers in operational policy config. That violates the component rule forbidding TODO/stub style placeholders in the repository state.
