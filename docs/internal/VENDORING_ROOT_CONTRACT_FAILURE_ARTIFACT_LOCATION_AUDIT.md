# Vendoring root contract failure artifact location audit

Wave AG aligns the executable root-contract checkers with the cleaned Vendoring root contract.

## Finding

The PowerShell and Bash root-contract checkers created failure markers under a root-level `.report/` directory. That directory is not allowed by the current root contract, so a failed check could create new forbidden root residue while reporting a root-contract violation.

## Change

- Moved root-contract failure marker output from `.report/gate-flag-root-contract.fail` to `build/reports/gate/root-contract.fail`.
- Documented the failure-artifact location in `.gate/contract/README.md`.

## Scope intentionally not changed

- No root folder expansion.
- No runtime proof phase.
- No Symfony source class rename.
