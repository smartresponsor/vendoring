# Vendoring root residue sync audit

## Scope

This wave reconciles the repository root and generated-residue boundary after the DI/container fixes and the previous canon waves.

## Findings

The active slice was structurally clean under `src/`, and the current scanners reported no source-placement or namespace violations. The remaining drift was repository hygiene residue:

- `.gitignore` was missing from the active slice even though the root contract documentation requires it.
- `.report/vendor-canon-scan.*` existed at the repository root, while prior root-contract cleanup moved generated report output under `build/reports/...`.
- `phpunit.log` existed at the repository root as a generated runtime artifact.
- Three historical backup files remained inside tracked source/config paths:
  - `config/packages/doctrine.yaml.bak_wave_r_20260430213057`
  - `config/packages/vendor_nelmio_api_doc.yaml.dist.bak_wave_r_20260430213057`
  - `src/Service/Ops/VendorTransactionOperatorPageBuilderService.php.bak_wave_r_20260430213057`

## Changes

- Restored `.gitignore` with canonical local-state, generated-report, log, backup, dependency, and environment ignores.
- Moved the existing canon scan artifacts from root `.report/` into `build/reports/canon/`.
- Retired root `.report/`, root `phpunit.log`, and the explicit stale backup files from source/config paths.

## Verification

```bash
php tools/vendoring-structure-scan.php --strict
php tools/vendoring-psr4-scan.php
php tools/vendoring-service-naming-audit.php
php tools/report/VendorMirrorEnforcerReport.php
php tools/report/VendorCanonicalStructureReport.php
```

All listed checks completed successfully in the patched snapshot.

## Residual notes

Historical docs still mention retired class names as audit history. Those are intentionally not treated as active source drift unless they appear in runtime config, current tests, or current guard logic.
