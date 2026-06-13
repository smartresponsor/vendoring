# Vendoring root contract executable sync patch manifest

## Wave

Wave AF: root contract executable sync

## Touched files

- `.gate/contract/ps1/root-contract-check.ps1`
- `.gate/contract/sh/root-contract-check.sh`
- `.gate/policy/acceptable/daily-proposal.yml`
- `.gate/policy/acceptable/root-contract-fail-proposal.yml`
- `docs/internal/VENDORING_ROOT_CONTRACT_EXECUTABLE_SYNC_AUDIT.md`
- `docs/internal/VENDORING_ROOT_CONTRACT_EXECUTABLE_SYNC_PATCH_MANIFEST.md`

## Deleted files

None.

## Validation notes

- Bash root-contract checker was run against the patched snapshot and returned `Root contract OK` after removing the transient `.report` directory created by the intentional first failing check.
- PowerShell checker was authored without `GetRelativePath`, `TrimStart`, or `TrimEnd` overloads for Windows PowerShell 5.1 compatibility.
