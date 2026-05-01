# Vendoring app env resolver instance contract patch manifest

## Modified files

- `src/ServiceInterface/Runtime/VendorAppEnvResolverServiceInterface.php`
- `src/Service/Runtime/VendorAppEnvResolverService.php`
- `src/Controller/Vendor/VendorLocalDevController.php`
- `src/Service/Observability/VendorRuntimeLoggerService.php`
- `src/Service/Observability/VendorRuntimeMetricCollectorService.php`

## Added files

- `docs/internal/VENDORING_APP_ENV_RESOLVER_INSTANCE_CONTRACT_AUDIT.md`
- `docs/internal/VENDORING_APP_ENV_RESOLVER_INSTANCE_CONTRACT_PATCH_MANIFEST.md`

## Deleted files

None.

## Validation intent

- `composer dump-autoload`
- `php bin/console lint:container`
- `php tests/bin/interface-alias-smoke.php`
