# Vendoring Wave AB — repo map retired root exclude sync audit

## Scope

This wave keeps the repository-map helper aligned with the current root cleanup contract.

## Finding

`.commanding/ps1/repo-map-builder.ps1` still excluded `.release` and `.smoke` by default. Those folders were already retired from the Vendoring root contract, so keeping them in the default hidden directory list could mask their accidental reappearance during local audits.

## Changes

- Removed `.release` from the default repo-map exclude list.
- Removed `.smoke` from the default repo-map exclude list.
- Left `.commanding`, `.gate`, `.intelligence`, `.canonization`, and `.dist` untouched because this wave does not redefine operator/tooling or distribution-policy folders.

## Deletions

No filesystem deletions are required in this wave.

## Validation

The change is PowerShell text-only and does not alter application runtime code.
