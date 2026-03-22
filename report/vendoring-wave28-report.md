# Vendoring wave28 report

- Active base: wave27 cumulative snapshot
- Scope: live Vendor metric HTTP flow

## Change
- Fixed currency-scope propagation in `src/Controller/Vendor/Metric/VendorMetricController.php`
- `trends()` now reads `currency` from query params and passes it to `VendorMetricServiceInterface::trends(...)`

## Why
- After wave25, `VendorMetricService::trends()` became currency-aware
- The HTTP controller still accepted tenant/from/to/bucket only and silently dropped currency scope
- This caused the trends endpoint to diverge from the overview endpoint and from the service contract

## Validation
- php -l on changed PHP file: PASS
- vendoring-structure-scan --strict: PASS
- vendoring-psr4-scan --strict: PASS
- vendoring-missing-class-scan-v3 --strict --limit=500: PASS
- vendoring-quality-gate-v3: PASS
