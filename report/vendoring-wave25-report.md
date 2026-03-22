# Vendoring Wave25 Report

- Scope: live Vendor metric layer
- Change: made `VendorMetricService::trends()` currency-aware by extending the interface and implementation with an optional `$currency = 'USD'` parameter and forwarding it into `overview()`
- Reason: after wave23, `overview()` is currency-scoped, but `trends()` still hard-coded the broader default path by not allowing callers to pass currency through
- Risk profile: low; change is backward-compatible because the new parameter is optional
