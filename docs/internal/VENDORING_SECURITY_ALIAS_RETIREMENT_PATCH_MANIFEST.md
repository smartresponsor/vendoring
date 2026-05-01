# Vendoring Security Alias Retirement Patch Manifest

## Modified files

- `config/component/services.yaml`
- `docs/internal/VENDOR_API_KEY_CANON.md`

## Added files

- `docs/internal/VENDORING_SECURITY_ALIAS_RETIREMENT_AUDIT.md`
- `docs/internal/VENDORING_SECURITY_ALIAS_RETIREMENT_PATCH_MANIFEST.md`

## Removed files

- `src/Service/Security/VendorSecurityService.php`
- `src/ServiceInterface/Security/VendorSecurityServiceInterface.php`
- `.release/`
- `.smoke/`

## Validation commands

```bash
composer dump-autoload
php bin/console lint:container
php tests/bin/interface-alias-smoke.php
```
