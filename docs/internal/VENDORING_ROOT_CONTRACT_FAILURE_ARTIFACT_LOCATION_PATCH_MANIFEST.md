# Vendoring root contract failure artifact location patch manifest

## Wave

AG - root contract failure artifact location cleanup

## Touched files

- `.gate/contract/ps1/root-contract-check.ps1`
- `.gate/contract/sh/root-contract-check.sh`
- `.gate/contract/README.md`
- `docs/internal/VENDORING_ROOT_CONTRACT_FAILURE_ARTIFACT_LOCATION_AUDIT.md`
- `docs/internal/VENDORING_ROOT_CONTRACT_FAILURE_ARTIFACT_LOCATION_PATCH_MANIFEST.md`

## Deleted files

None.

## Validation expectation

Run root-contract checkers and confirm that any failure marker is emitted under `build/reports/gate/` rather than a root `.report/` directory.
