# Vendoring Wave 75 Report

## Scope
- Removed forbidden root-level non-dot PHP artifact duplicated outside canonical trees.
- Strengthened root-structure guards so runtime/test PHP files must live under canonical directories.

## Changes
- Removed `transaction-json-surface-smoke.php` from repository root.
- Extended `CanonicalRootStructureContractTest` to forbid root-level non-dot PHP files.
- Extended `tests/bin/root-structure-smoke.php` with the same guard.

## Outcome
- Root-level executable/test PHP drift is now blocked.
- Canonical runtime/test locations remain `src/`, `tests/`, `bin/`, `public/`, and dot-prefixed root tooling files only.
