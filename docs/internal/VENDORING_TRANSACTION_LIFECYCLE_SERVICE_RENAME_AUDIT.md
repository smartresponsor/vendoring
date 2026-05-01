# Vendoring Wave N — Transaction lifecycle service rename audit

## Scope

This pass removes the remaining generic `Manager` service naming from the active transaction contour. The previous class/interface pair was mirrored, but `Manager` is semantically weak for the type-oriented service layer because it does not identify the actual business responsibility.

## Changes

- `VendorTransactionManagerService` was renamed to `VendorTransactionLifecycleService`.
- `VendorTransactionManagerServiceInterface` was renamed to `VendorTransactionLifecycleServiceInterface`.
- Transaction controllers now inject the lifecycle interface and use the `$transactionLifecycle` property name.
- Symfony service aliases were updated in both `config/component/services.yaml` and `config/vendor_services_transactions.yaml`.
- Layer-3 naming examples now point to the lifecycle service pair.

## Legacy files removed by apply script

- `src/Service/Transaction/VendorTransactionManagerService.php`
- `src/ServiceInterface/Transaction/VendorTransactionManagerServiceInterface.php`

## Verification

```bash
composer dump-autoload
php bin/console lint:container
php tests/bin/interface-alias-smoke.php
```
