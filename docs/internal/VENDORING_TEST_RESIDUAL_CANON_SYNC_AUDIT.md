# Vendoring test residual canon sync audit

## Scope

This wave aligns active tests, support doubles, and smoke scripts with the current Vendoring canon after the DI/container repair and previous structure/tooling cleanup.

## Findings

The active `src/` service surface already used the canonical names, but multiple tests and smoke scripts still referenced retired symbols:

- `*ViewBuilder*` service/interface names after the source was renamed to `*ProjectionBuilder*`.
- `VendorFile*` concrete service names after file-backend detail was removed from public service names.
- `VendorTransactionManagerService` after lifecycle naming became canonical.
- `VendorStatementExporterPDFService` after acronym normalization to `Pdf`.
- `Vendor*Type` form tests after forms became `Vendor*Form`.
- `VendorService` after core service naming became `VendorCoreService`.
- obsolete `VendorSecurityService` alias tests after the wrapper was retired.

## Changes

- Renamed active test files/classes to match current service/form naming.
- Updated mocks, imports, smoke scripts, and support fakes to current interfaces and services.
- Replaced the brittle hard-coded interface alias test with a config-driven alias scan over `config/component/services.yaml`.
- Removed obsolete security alias tests for the retired wrapper.

## Verification

- `php -l` passed for all changed/added PHP files in this wave.
- `php tools/vendoring-service-naming-audit.php` reports zero violations.
- Residual grep over active `tests`, `config`, and `tools` contains only the explicit retired-name denylist in `tools/vendoring-service-naming-audit.php`.
