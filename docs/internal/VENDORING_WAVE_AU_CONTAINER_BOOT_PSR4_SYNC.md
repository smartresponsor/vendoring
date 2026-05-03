# Vendoring Wave AU — Container Boot + Test PSR-4 Sync

## Purpose

Wave AU follows the local runtime proof log after Wave AT. Composer install completed, but Symfony container boot failed on an unsupported Doctrine Bundle configuration option, and optimized autoload reported test PSR-4 drift.

## Changes

- Removed unsupported `doctrine.orm.validate_xml_mapping` from `config/packages/doctrine.yaml`.
- Preserved the two-connection split: `user_data` for vendor business data and `app_data` for internal SQLite state.
- Renamed test files whose class names already used the canonical `Vendor*` prefix.
- Re-scoped legacy `Tests\...` test namespaces into `App\Vendoring\Tests\...`.
- Removed residual `VendorPayoutEntity` namespace buckets from tests/support where the actual folder is `Payout`.
- Updated in-memory payout repository imports to the canonical test support namespace.

## Expected local confirmation

```powershell
composer dump-autoload -o
php bin/console lint:container
php bin/console debug:container --env=dev
php bin/console cache:clear --env=dev -vvv
```

## Notes

This wave does not add production domain behavior. It unblocks container configuration parsing and removes autoload warnings from active tests.
