# Vendoring Wave R — patch manifest

## Touched files

- `config/packages/doctrine.yaml`
- `config/packages/vendor_nelmio_api_doc.yaml.dist`
- `src/Service/Ops/VendorTransactionOperatorPageBuilderService.php`
- `docs/internal/VENDORING_DOCTRINE_MAPPING_AND_OPERATOR_LABEL_CLEANUP_AUDIT.md`
- `docs/internal/VENDORING_DOCTRINE_MAPPING_AND_OPERATOR_LABEL_CLEANUP_PATCH_MANIFEST.md`

## Deleted files

None.

## Validation targets

- `composer dump-autoload`
- `php bin/console lint:container`
- `php tests/bin/interface-alias-smoke.php`
