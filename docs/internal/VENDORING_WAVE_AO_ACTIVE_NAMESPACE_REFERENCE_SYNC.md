# Vendoring Wave AO — active namespace reference sync

## Scope

Wave AO closes the remaining active plain `App\\...` namespace references found after the Wave AN label cleanup.

## Changes

- Updated demo fixture remember-me provider payloads from `App\\Security\\VendorRememberMeProvider` to `App\\Vendoring\\Security\\VendorRememberMeProvider`.
- Updated the ownership runtime synthetic probe with the same component-scoped provider payload.
- Strengthened `tools/canon/vendor-canon-scan.mjs` so active roots fail on plain `App\\...` namespace references outside `App\\Vendoring\\...`.
- Kept `config/reference.php` excluded from that literal-reference rule because it is Symfony-generated reference text and intentionally contains a generic documentation example.

## Validation

- `node tools/canon/vendor-canon-scan.mjs`
- `php tools/canon/vendor-scan.php`
- `php -l` for changed PHP files

## Result

Active fixtures, runtime probes, and canon tooling now align with the component-scoped `App\\Vendoring\\...` namespace contract.
