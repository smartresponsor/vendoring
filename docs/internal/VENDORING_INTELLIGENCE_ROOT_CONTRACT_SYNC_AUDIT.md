# Vendoring Wave Y — Intelligence root contract sync audit

## Scope

This wave keeps the repository root contract aligned after prior cleanup waves removed legacy root deployment/smoke/release payloads and left `.intelligence` as the active lowercase automation folder.

## Findings

- `.intelligence/intelligence-engine.ps1` resolved configuration from `.Intelligence/intelligence.json`, while the actual committed folder is `.intelligence`.
- `.intelligence/intelligence-install.ps1` mirrored files into `.Intelligence`, which could recreate a mixed-case root dot-folder on Windows/macOS-sensitive workflows.
- `.gate/contract/contract.json` still allowed retired root dot-folders `.deploy`, `.release`, and `.smoke`.
- `.ide/` is intentionally kept as ecosystem/tooling inspector payload, but the root gate contract did not explicitly allow it.

## Changes

- Normalized Intelligence script references to `.intelligence`.
- Added `.Intelligence/` to `.gitignore` as a guard against legacy mixed-case mirror output.
- Removed retired `.deploy`, `.release`, and `.smoke` entries from the allowed root dot-folder list.
- Added `.ide` to the allowed root dot-folder list because `.ide/sr_default_inspector.xml` is not JetBrains workspace residue.

## Non-goals

- No `.commanding` migration.
- No deletion of `.intelligence` or `.ide`.
- No namespace or service-container changes.
