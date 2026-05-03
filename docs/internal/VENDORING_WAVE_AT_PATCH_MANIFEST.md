# Vendoring Wave AT Patch Manifest

## Touched files

- `.github/workflows/docs.yml`
- `.github/workflows/quality.yml`
- `.github/workflows/release-candidate.yml`
- `.github/workflows/runtime.yml`
- `config/packages/doctrine.yaml`
- `config/packages/nelmio_api_doc.yaml`
- `config/packages/vendor_bridge.yaml`
- `docs/ARCHITECTURE_REVIEW_120_CHECKLIST.md`
- `docs/internal/VENDORING_WAVE_AT_PATCH_MANIFEST.md`
- `docs/internal/VENDORING_WAVE_AT_RUNTIME_PREFLIGHT_SYNC.md`
- `docs/release/RC_RUNTIME_ACTIVATION.md`
- `src/DataFixtures/VendorOwnershipDemoFixture.php`
- `tests/bin/no-example-wording-repository-smoke.php`
- `tests/bin/no-placeholder-repository-smoke.php`
- `tests/bin/runtime-activation-smoke.php`

## Deleted files

None.

## Notes

This patch is an overlay-only runtime/preflight synchronization wave. It does not delete files and does not change production service logic.
