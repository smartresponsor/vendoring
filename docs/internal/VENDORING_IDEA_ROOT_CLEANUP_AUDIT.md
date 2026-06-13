# Vendoring IDEA Root Cleanup Audit

## Scope

Wave X removes checked-in JetBrains IDE workspace metadata from the repository root and adds a repository-local ignore guardrail.

## Findings

- `.idea/` contained IDE/workspace metadata, including command-line tool cache XML, inspection profiles, project/module files, and local workspace settings.
- These files are not application runtime, deploy payload, Symfony source, generated delivery artifact, or operator tooling contract.
- Keeping `.idea/` at the root makes structural scans noisier and can reintroduce user-machine-specific state into future patches.

## Canonical decision

- `.idea/` is retired from the active repository payload.
- `.gitignore` now explicitly ignores `.idea/`.
- Runtime `logs/` are also ignored at root because previous waves moved component/operator runtime logs into owned subtrees.

## Out of scope

- `.ide/sr_default_inspector.xml` is left untouched because it appears to be an ecosystem/tooling inspector artifact, not a JetBrains workspace folder.
- `.commanding/` is left untouched; it remains the operator/tooling layer.
