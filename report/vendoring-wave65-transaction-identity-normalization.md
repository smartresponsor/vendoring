# Vendoring wave 65 — transaction identity normalization

## Scope
- Normalize `vendorId` and `orderId` inside `VendorTransactionManager`
- Reject blank-after-trim identities before duplicate lookup and persistence
- Add smoke and unit/controller coverage for the identity invariant

## Delivered
- `src/Service/VendorTransactionManager.php`
- `tests/Unit/Transaction/VendorTransactionManagerTest.php`
- `tests/Unit/Controller/VendorTransactionControllerTest.php`
- `tests/bin/transaction-identity-smoke.php`
- `tests/bin/smoke.php`
- `composer.json`

## Notes
- This wave is built strictly on top of `vendoring-64-null-project-idempotency-cumulative.zip`
- The invariant now lives in the manager, not only in the HTTP entrypoint
