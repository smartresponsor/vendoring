# Vendoring Wave 76 Report

## Summary
Removed stray root-level wave artifact markdown file and strengthened root-structure guards.

## Changes
- removed root `vendoring-wave72-transaction-json-parity.md`
- extended `CanonicalRootStructureContractTest` to forbid `vendoring-wave*.md` artifacts in repository root
- extended `tests/bin/root-structure-smoke.php` with the same guard
- wired `test:root-structure-stray` in composer quality pipeline

## Validation
- php -l tests/Unit/Infrastructure/CanonicalRootStructureContractTest.php
- php -l tests/bin/root-structure-smoke.php
- php tests/bin/root-structure-smoke.php
- php tests/bin/smoke.php
