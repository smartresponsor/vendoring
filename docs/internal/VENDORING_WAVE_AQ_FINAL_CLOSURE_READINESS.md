# Vendoring Wave AQ — Final Closure Readiness

## Purpose

Wave AQ is a closure-oriented pass after the structural, namespace, mirror-contract, root-residue, and active-test cleanup waves. It intentionally avoids production-code churn because the active source/config/tooling checks are already green.

## Current canonical state

- Component namespace: `App\\Vendoring\\...`.
- Production source folder: type-oriented `src/` structure.
- Service contracts mirror concrete services through `src/ServiceInterface/...`.
- Repository contracts mirror repositories through `src/RepositoryInterface/...`.
- Projection terminology is canonical; retired `View`/`ViewBuilder` naming remains only in historical audit notes.
- Form classes use `Vendor*Form` naming while still extending Symfony form types where needed.
- `VendorEntity` is treated as a PHP/Doctrine class name only, not as a user-facing/business label.
- Runtime/report artifacts are expected under `build/reports/...`, not retired root `.report/`.
- Retired root folders such as `.deploy`, `.smoke`, `.release`, `.report`, `.idea`, and `.Intelligence` are not expected as active roots.

## Verified checks

The following checks were run against the Wave AP cumulative snapshot before this closure note was added:

```text
php tools/vendoring-structure-scan.php --strict
# Forbidden placement hits: 0
# Duplicate-segment directories: 0
# Legacy-path hits: 0

php tools/vendoring-psr4-scan.php
# Issue count: 0

php tools/vendoring-service-naming-audit.php
# violations: []

php tools/report/VendorMirrorEnforcerReport.php
# exit code: 0

php tools/report/VendorCanonicalStructureReport.php
# exit code: 0

node tools/canon/vendor-canon-scan.mjs
# vendor-canon-scan ok

php tools/canon/vendor-scan.php
# Vendoring canon scan: OK

php tools/vendoring-missing-class-triage.php --limit=2000
# Issue count: 0
# vendor-entity-reference-missing: 0
# serviceinterface-missing: 0
# namespace-noise: 0
```

## Remaining non-blocking residues

The remaining matches for retired terms are historical audit/patch-manifest records or intentional class-name references. They are not treated as active drift:

- Historical docs may mention old names such as `VendorSecurityService`, `VendorTransactionManagerService`, `VendorFile*`, `ViewBuilder`, `Vendor*View`, `VendorTfidf*`, or `VendorStatementExporterPDFService` when documenting a completed rename.
- `VendorEntity` remains valid where it names the Doctrine/PHP class or a test whose purpose is entity compatibility.
- Generic strings containing `.deploy`, `.smoke`, `.release`, or `.report` may still occur in documentation or commands where the text is historical or describes report output behavior; active root placement checks are green.

## Closure decision

Vendoring is structurally ready for the next phase. Further work should now be driven by runtime proof, DI/container execution, PHPUnit/PHPStan failures, or business capability gaps rather than additional naming-only sweeps.
