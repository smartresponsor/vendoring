# Vendoring Wave 58 — Transaction Status Policy

## What changed
- Added explicit transaction status transition policy contract and concrete service.
- Wired `VendorTransactionManager` to normalize and validate transitions before mutating state.
- Hardened `VendorTransactionController` so `status` is required for update requests and invalid transitions return HTTP 422.
- Added policy-focused smoke and unit coverage.

## Why this wave matters
Before this wave, transaction status updates were effectively unconstrained: any status string could be written to `VendorTransaction` from the HTTP entrypoint. That made the transaction flow structurally Symfony-oriented but semantically weak.

This wave moves the transition rules into a dedicated policy service, keeps the HTTP layer thin, and gives the component a first explicit state-machine guard for transaction lifecycle changes.

## Added coverage
- `VendorTransactionStatusPolicyTest`
- extended `VendorTransactionControllerTest`
- `tests/bin/transaction-policy-smoke.php`
