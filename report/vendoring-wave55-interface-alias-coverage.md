# Vendoring wave 55 — interface alias coverage

## Scope
- closed the next DI/runtime gap after wave54 by making canonical interface wiring exhaustive instead of partial
- extended service configuration so repository and service interfaces with existing concrete classes are all explicitly aliased
- added smoke and unit coverage for interface alias completeness

## Findings
- `config/services.yaml` still covered only a subset of canonical `RepositoryInterface/*` and `ServiceInterface/*`
- several concrete classes already existed but remained without explicit alias coverage:
  - vendor analytics/attachment/document/ledger binding/security repositories
  - crm, billing, document, media, passport, profile, vendor, double-entry, payout bridge, settlement calculator services
- this left the component partially autowireable rather than canonically and predictably wired

## Changes
- expanded `config/services.yaml` with the missing interface-to-concrete aliases
- added `tests/Unit/Infrastructure/InterfaceAliasCoverageTest.php`
- added `tests/bin/interface-alias-smoke.php`
- extended `composer.json` `test:di` to include the new smoke/unit coverage

## Result
- the DI layer now declares a substantially more complete canonical interface surface
- future constructor migrations toward interface-based dependencies have a lower chance of silently missing service aliases
