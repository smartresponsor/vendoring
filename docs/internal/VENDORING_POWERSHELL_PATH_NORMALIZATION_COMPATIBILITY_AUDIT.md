# Vendoring PowerShell path normalization compatibility audit

Wave AE removes fragile PowerShell path normalization calls from repository tooling.

## Findings

- Some repository helper scripts still used `TrimStart`/`TrimEnd` overloads with path separator arguments.
- Similar path-normalization shapes already failed during patch application on Windows PowerShell 5.1 in this cleanup sequence.
- These scripts are operator/gate tooling, so the risk is not business logic drift, but broken local automation on the target Windows host.

## Changes

- Replaced separator trimming in `.commanding/ps1/delta-slice-builder.ps1` with explicit `StartsWith`/`Substring` loops.
- Replaced root and relative-path separator trimming in `.commanding/ps1/repo-map-builder.ps1` with explicit loops.
- Replaced ZIP entry leading-slash trimming in `.gate/linting/ps1/archive-flat-root-check.ps1` with explicit loops.

## Non-goals

- No business-layer code changes.
- No namespace changes.
- No repository-wide destructive cleanup.
