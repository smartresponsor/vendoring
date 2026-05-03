# Vendoring Wave AX — PHPStan 70 Residual Sync

## Scope

Wave AX follows the local PHPStan run that reduced the active error set to 70 findings after Wave AW.

The patch is intentionally narrow:

- fix the remaining production `VendorOwnershipWriteService` PHPStan findings;
- normalize stale test/support aliases to canonical Vendoring entity class names;
- keep Doctrine entities intact and avoid getter-only churn for static-analysis noise.

## Production fixes

- Removed the remaining always-true payment `instanceof` branch in `VendorOwnershipWriteService`.
- Replaced accidental undefined `$status` log payload references with the corresponding DTO status values.

## Test/support fixes

- `FakeVendorTransactionLifecycle` now uses canonical `VendorTransactionEntity` and `VendorTransactionDataValueObject` types.
- Transaction lifecycle tests now reference `VendorTransactionEntity` instead of retired `VendorTransaction` aliases.
- Profile service/projection tests now reference `VendorProfileEntity`.
- API key tests now reference `VendorApiKeyEntity`.
- User assignment role normalization test now references `VendorUserAssignmentEntity`.
- Payout account repository test documents the generic `ObjectRepository<VendorPayoutAccountEntity>` mock type.

## Verification performed in sandbox

- PHP syntax check passed for every touched PHP file.
- Residual grep found no active references to the stale aliases reported in the 70-error PHPStan log:
  - `VendorTransactionEntityEntity`
  - `VendorTransactionEntityDataValueObject`
  - `VendorTransactionEntityLifecycleService`
  - `App\\Vendoring\\Entity\\VendorTransaction`
  - `App\\Vendoring\\Entity\\VendorProfile`
  - `App\\Vendoring\\Entity\\VendorUserAssignment`
  - `App\\Vendoring\\Entity\\VendorApiKey`

## Follow-up

After applying this patch, run:

```powershell
composer dump-autoload -o
vendor\bin\phpstan
```

Any remaining findings should now be a smaller tail, likely around PHPUnit generic typing or business-level test expectations rather than broad namespace drift.
