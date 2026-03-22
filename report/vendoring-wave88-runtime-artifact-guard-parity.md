# Vendoring Wave 88 — Runtime Artifact Guard Parity

## Scope
Bring `test:root-runtime-artifacts` to full parity with the already existing guard surface.

## Changes
- extended `composer.json`
  - `test:root-runtime-artifacts` now runs:
    - `tests/bin/root-runtime-artifact-smoke.php`
    - `CanonicalRootRuntimeArtifactContractTest`
- no source/business code changes
- no structural tree changes

## Reason
The repository already had both layers:
- a smoke script for committed runtime artifacts
- a PHPUnit contract test for the same invariant

But the Composer entrypoint executed only the smoke layer. That left the guard partially wired.

## Result
The runtime-artifact guard is now fully orchestrated through Composer and aligned with the other canonical guard slices.
