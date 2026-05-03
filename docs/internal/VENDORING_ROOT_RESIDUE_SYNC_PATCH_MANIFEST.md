# Vendoring root residue sync patch manifest

## Added or updated files

- `.gitignore`
- `build/reports/canon/vendor-canon-scan.json`
- `build/reports/canon/vendor-canon-scan.txt`
- `docs/internal/VENDORING_ROOT_RESIDUE_SYNC_AUDIT.md`
- `docs/internal/VENDORING_ROOT_RESIDUE_SYNC_PATCH_MANIFEST.md`

## Explicit retired residue

The apply script removes these paths with backup when present:

- `.report/`
- `phpunit.log`
- `config/packages/doctrine.yaml.bak_wave_r_20260430213057`
- `config/packages/vendor_nelmio_api_doc.yaml.dist.bak_wave_r_20260430213057`
- `src/Service/Ops/VendorTransactionOperatorPageBuilderService.php.bak_wave_r_20260430213057`

## Rationale

This is a repository-hygiene wave only. It does not rewrite working business code and does not perform a full repository overwrite. It keeps generated evidence under `build/reports/canon/` and keeps the root clean for the current root-contract direction.
