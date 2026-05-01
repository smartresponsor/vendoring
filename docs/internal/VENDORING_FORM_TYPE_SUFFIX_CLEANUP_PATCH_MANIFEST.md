# Vendoring form type suffix cleanup patch manifest

## Added / updated

- `src/Form/Ops/VendorTransactionCreateForm.php`
- `src/Form/Ops/VendorTransactionStatusUpdateForm.php`
- `src/Controller/Vendor/VendorTransactionOperatorController.php`
- `docs/internal/VENDORING_FORM_TYPE_SUFFIX_CLEANUP_AUDIT.md`
- `docs/internal/VENDORING_FORM_TYPE_SUFFIX_CLEANUP_PATCH_MANIFEST.md`

## Removed by apply script

- `src/Form/Ops/VendorTransactionCreateType.php`
- `src/Form/Ops/VendorTransactionStatusUpdateType.php`

## Validation targets

- `composer dump-autoload`
- `php bin/console lint:container`
- `php tests/bin/interface-alias-smoke.php`
