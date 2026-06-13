# Vendoring final residual structure audit

## Scope

This audit was produced from the `VendoringFri.zip` current slice after the DI/container fixes that were applied outside the previous assistant wave.

The pass focused on structure, namespace consistency, canonical naming, mirrored contracts, and cleanup guardrails.

## Findings

### Source tree

- `src/` uses the component-scoped namespace root `App\Vendoring\...`.
- The source-tree structure is type-oriented and Symfony-oriented.
- The structural scanner reports no forbidden placements, no duplicate-segment directories, and no legacy-path hits.
- The missing-class scanner reports no unresolved `App\Vendoring\...` class references.

### Residual drift fixed in this wave

The remaining drift was not primarily in business/runtime source. It was in guard/report tooling that still reflected older cleanup phases:

- `tools/vendoring-psr4-scan.php` expected plain `App\...` namespaces and therefore reported false mismatches for valid `App\Vendoring\...` classes.
- `tools/vendoring-service-naming-audit.php` still enforced the retired `VendorEntity*Service` naming idea, even though current canon keeps `VendorEntity` reserved for Doctrine/PHP entity terminology and uses `Vendor*Service` service names.
- `tools/report/VendorMirrorEnforcerReport.php` used a hard-coded, stale mirror pair list that referenced retired root service paths such as `src/Service/VendorService.php`.
- Several report headers still said `VendorEntity ... report`, which made the report surface look entity-scoped rather than component-scoped.

## Verification after patch

The following checks were executed against the patched working tree:

```text
php tools/vendoring-psr4-scan.php
# Issue count: 0

php tools/vendoring-service-naming-audit.php
# violations: []

php tools/report/VendorMirrorEnforcerReport.php
# exit code: 0

php tools/report/VendorCanonicalStructureReport.php
# exit code: 0
```

## Remaining work estimate

At the architecture/canonization layer, Vendoring is now in a late cleanup state:

- source structure / namespace / mirror contract tooling: about 90-93% complete;
- remaining cleanup is mostly stale tests, old documentation references, and optional report wording cleanup;
- runtime-proof work such as full PHPUnit/PHPStan/container build remains a separate final proof phase.

## Next recommended wave

Next wave should focus on stale test namespace/name references only, especially old `ViewBuilder`, `VendorTransactionManagerService`, `VendorFile*`, `Vendor*PDFService`, and `VendorService` references under `tests/`.
