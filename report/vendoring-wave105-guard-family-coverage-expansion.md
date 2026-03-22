# Vendoring Wave 105 - Guard Family Coverage Expansion

## Scope
- Extend composer-guard parity coverage to include the already-existing `test:no-example-command-help` and `test:no-legacy-vendor-script` slices.

## Changes
- Updated `tests/bin/composer-guard-parity-smoke.php`.
- Updated `tests/Unit/Infrastructure/ComposerGuardScriptParityTest.php`.

## Result
- The parity guard now covers the full active repository/source/config guard family instead of stopping short of the latest command-help and legacy-script slices.
