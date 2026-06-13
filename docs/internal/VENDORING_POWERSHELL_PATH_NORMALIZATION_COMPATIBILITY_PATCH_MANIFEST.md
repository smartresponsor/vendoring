# Vendoring PowerShell path normalization compatibility patch manifest

## Wave

AE - PowerShell path normalization compatibility cleanup.

## Touched files

- `.commanding/ps1/delta-slice-builder.ps1`
- `.commanding/ps1/repo-map-builder.ps1`
- `.gate/linting/ps1/archive-flat-root-check.ps1`
- `docs/internal/VENDORING_POWERSHELL_PATH_NORMALIZATION_COMPATIBILITY_AUDIT.md`
- `docs/internal/VENDORING_POWERSHELL_PATH_NORMALIZATION_COMPATIBILITY_PATCH_MANIFEST.md`

## Deleted files

None.

## Verification

Run the repository gate/tooling commands that consume these scripts on Windows PowerShell 5.1.
