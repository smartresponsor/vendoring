# Vendoring Wave 57 — transaction route contracts

## What changed
- removed legacy `routes_vendor_transactions.yaml` import from `config/routes.yaml` and kept controller attribute routing as the single source of truth
- aligned `config/vendor_services_transactions.yaml` with the real `VendorTransactionManager` constructor and removed stale `$repo` wiring
- extended `VendorTransactionRepositoryInterface` with `findOneByIdAndVendorId()`
- updated `VendorTransactionController::updateStatus()` to use vendor-scoped repository lookup instead of scanning a vendor list from request payload/query
- added unit coverage for the vendor-scoped transaction status update flow
- added transaction route smoke coverage

## Why
The previous transaction status path still depended on request-sourced `vendorId` plus an in-controller scan over `findByVendorId()`. That kept HTTP entrypoint logic coupled to selection details and left a legacy route/config drift in place.
