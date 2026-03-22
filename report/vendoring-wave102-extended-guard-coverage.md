# Vendoring Wave 102 — Extended Guard Coverage

This wave closes the orchestration gap between existing repository/config/source guard files and the master guard parity layer.

## Changes
- extended `ComposerGuardScriptParityTest` to cover:
  - `test:no-stub-repository`
  - `test:no-example-config`
  - `test:no-example-repository`
  - `test:no-example-wording-repository`
  - `test:app-namespace-repository`
- extended `tests/bin/composer-guard-parity-smoke.php` with the same expected script set
- extended `tests/bin/smoke.php` so master smoke now requires the full repository/config/source guard family to be present in Composer orchestration

## Why
The active cumulative slice already contained these guard scripts physically, but the master smoke/parity layer still validated only a subset of them. This wave makes guard coverage match the real guard surface.
