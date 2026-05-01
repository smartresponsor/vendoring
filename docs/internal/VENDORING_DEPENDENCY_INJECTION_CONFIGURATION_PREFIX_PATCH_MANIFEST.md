# Vendoring DependencyInjection Configuration Prefix Patch Manifest

## Added

- `src/DependencyInjection/VendorConfiguration.php`
- `docs/internal/VENDORING_DEPENDENCY_INJECTION_CONFIGURATION_PREFIX_AUDIT.md`
- `docs/internal/VENDORING_DEPENDENCY_INJECTION_CONFIGURATION_PREFIX_PATCH_MANIFEST.md`

## Modified

- `src/DependencyInjection/VendoringExtension.php`
- `config/component/component.yaml`

## Retired by apply script

- `src/DependencyInjection/Configuration.php`

## Validation targets

```bash
composer dump-autoload
php bin/console lint:container
php tests/bin/interface-alias-smoke.php
```
