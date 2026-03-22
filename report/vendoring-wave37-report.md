# Vendoring Wave37 Report

- Active base: wave36 cumulative snapshot
- Scope: live metric service-layer

## Changes
- Preserved full metric scope in `VendorMetricService::trends()` payload.
- Aligned `VendorMetricServiceInterface` phpdoc with actual overview/trends payload shapes.

## Why
- `trends()` already accepted `tenantId`, `vendorId`, `from`, `to`, `bucket`, and `currency`, but returned only aggregated numeric fields plus `bucket`/`period`.
- This lost the request scope on the service boundary and left the interface docs behind the real payload shape.

## Result
- Trend rows now carry `tenantId`, `vendorId`, `from`, `to`, and `currency` in addition to `bucket`, `period`, and totals.
- Metric service contract docs are now synchronized with the live implementation.
