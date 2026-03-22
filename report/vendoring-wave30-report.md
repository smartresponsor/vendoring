# Vendoring Wave 30 Report

Scope: live `src/Service/Vendor/Metric` semantic alignment.

## Change
- fixed `VendorMetricService::trends()` so the public `$bucket` parameter is no longer silently ignored
- returned trend rows now include `bucket` alongside the existing `period` and metric fields

## Why
- after wave25 and wave28, metric trend flow already accepted `bucket` and `currency` from the HTTP layer
- `currency` was propagated, but `bucket` was still accepted only at the signature level and then discarded inside the service
- this wave makes the returned trend payload reflect the requested aggregation scope without inventing new repository semantics

## Validation
- `php -l src/Service/Vendor/Metric/VendorMetricService.php`
- `php tools/vendoring-structure-scan.php --strict`
- `php tools/vendoring-psr4-scan.php --strict`
- `php tools/vendoring-missing-class-scan-v3.php --strict --limit=500`
- `php tools/vendoring-quality-gate-v3.php`
