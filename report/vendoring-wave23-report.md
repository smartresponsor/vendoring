# Vendoring wave23 report

- Active base: cumulative snapshot wave22
- Scope: live `src/Service/Vendor` semantic mismatch cleanup

## Change

Fixed `App\Service\Vendor\Metric\VendorMetricService::overview()` so its public `$currency` parameter is no longer ignored.

Updated ledger summary calls to pass the currency filter into `LedgerEntryRepositoryInterface::sumByAccount(...)` for:
- `REVENUE`
- `REFUNDS_PAYABLE`
- `VENDOR_PAYABLE`

## Why

After wave22, the ledger repository contract and implementation already supported an optional currency filter. `VendorMetricService::overview()` still accepted `$currency = "USD"` but silently computed tenant/vendor totals across all currencies.

This wave aligns the live metric service with its own public method signature, without changing broader business semantics or interface topology.

## Verification

- `php -l src/Service/Vendor/Metric/VendorMetricService.php`
- `php tools/vendoring-structure-scan.php --strict`
- `php tools/vendoring-psr4-scan.php --strict`
- `php tools/vendoring-missing-class-scan-v3.php --strict --limit=500`
- `php tools/vendoring-quality-gate-v3.php`
