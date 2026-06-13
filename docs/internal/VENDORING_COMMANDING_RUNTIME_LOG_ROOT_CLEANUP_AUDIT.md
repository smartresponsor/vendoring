# Vendoring Commanding Runtime Log Root Cleanup Audit

## Scope

Wave O removes the remaining application-root `logs/` runtime residue created by the local `.commanding` operator helpers.

## Findings

- The repository root still contained `logs/actions.log`, which is generated runtime state and should not be part of the application tree.
- `.commanding/lib/ui.sh` wrote action and error logs to `$root/logs/*`, which recreated the root-level bucket.
- `.commanding/sh/health-lib.sh` wrote health runs to `$root/logs/health`, also recreating root runtime state.

## Changes

- `.commanding/lib/ui.sh` now writes operator runtime logs under `.commanding/logs/runtime/`.
- `.commanding/sh/health-lib.sh` now writes health runs under `.commanding/logs/health/`.
- Root `logs/actions.log` is removed from the cumulative snapshot and from touched installs.

## Boundary

This is an operator-runtime cleanup only. It does not move Docker or deployment files and does not change Symfony service wiring.
