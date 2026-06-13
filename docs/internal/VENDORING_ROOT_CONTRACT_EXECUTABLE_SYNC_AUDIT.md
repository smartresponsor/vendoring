# Vendoring root contract executable sync audit

Wave AF synchronizes the executable root-contract checks and policy proposals with the current machine-readable root contract.

## Findings

- `.gate/contract/contract.json` was already updated to require only `.gitignore`, `README.md`, and `composer.json` at the repository root.
- `.gate/contract/ps1/root-contract-check.ps1` and `.gate/contract/sh/root-contract-check.sh` still required root-level `MANIFEST.json` and still allowed older root folders such as `templates`, `tests`, `var`, and `assets`.
- The PowerShell checker still used a path normalization form based on `TrimEnd`, which is the same overload family that caused Windows PowerShell 5.1 failures in earlier apply scripts.
- Gate proposals still attempted to seed `MANIFEST.json` at root, contradicting the current Vendoring root contract.

## Changes

- Updated PowerShell and Bash root-contract checkers to match the current root contract.
- Removed root-level `MANIFEST.json` from required and allowed files.
- Restricted allowed non-dot directories to the current Vendoring list: `bin`, `build`, `config`, `delivery`, `deploy`, `docs`, `drivers`, `migrations`, `ops`, `public`, and `src`.
- Added explicit allowed dot-folder checking for `.canonization`, `.commanding`, `.consuming`, `.gate`, `.github`, `.ide`, and `.intelligence`.
- Removed the stale gate proposal that recreated `MANIFEST.json`.
- Reworded the root-contract failure proposal to describe the current Vendoring contract.

## Safety

- No source classes were renamed in this wave.
- No repository-wide delete or overwrite behavior is introduced.
- PowerShell path normalization avoids `GetRelativePath`, `TrimStart`, and `TrimEnd` overloads.
