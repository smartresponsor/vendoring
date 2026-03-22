# Vendoring wave36 report

- Active base: wave35 cumulative snapshot.
- Scope: live metric service-layer.
- Change: repaired payload-loss in `src/Service/Vendor/Metric/VendorMetricService.php`.
- Details: `overview()` already accepted `tenantId`, `vendorId`, `from`, `to`, and `currency`, but returned only numeric totals. The response payload now preserves the accepted metric scope alongside `revenue`, `refunds`, `payouts`, and `balance`.
- Safety: no route, DTO, repository contract, or calculation formula changes.
