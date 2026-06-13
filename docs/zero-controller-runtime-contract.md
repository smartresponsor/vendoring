# Vendoring Zero-Controller Runtime Contract

This component is expected to be consumed through Cruding catch-all routes and FQCN convention.

## Runtime dispatch

```text
URI
→ Cruding grammar
→ providerKey / routeKey
→ App\Vendoring\Service\Http\Vendor\...\*Service
→ optional App\Vendoring\Form\Vendor\...\*Type
```

## Forbidden

```text
src/Controller/
src/ControllerTrait/
retired component route file
Symfony Route attributes inside component code
```

## Route-map registry

```text
config/platform/routes.platform.yaml
config/platform/routes.crud.yaml
config/platform/routes.business.yaml
config/platform/routes/crud/vendor.yaml
config/platform/routes/crud/vendor.attachment.document.yaml
config/platform/routes/crud/vendor.attachment.media.yaml
config/platform/routes/business/vendor.yaml
```

## Required smoke

```bash
php tools/smoke/vendoring-zero-controller-audit.php
php tests/bin/vendoring-zero-controller-hardening-smoke.php
php tests/bin/vendor-route-map-coverage-smoke.php
```
## Smoke script policy

Operational smoke scripts may contain negative-check strings such as retired route-file names or retired controller paths. Strict repository hardening scans production code, config, unit contracts, and reports, but does not treat `tests/bin` scripts themselves as runtime artifacts.

## Runtime service contract

Every route-map referenced runtime service under `src/Service/Http/Vendor` must:

```text
use namespace App\Vendoring\Service\Http\Vendor\...
end with Service
be final
expose __invoke()
declare an explicit return type
avoid controller/route vocabulary
```

Every form under `src/Form/Vendor` must:

```text
use namespace App\Vendoring\Form\Vendor\...
end with Type
extend Symfony AbstractType
```

Artifact expectations are listed in:

```text
docs/runtime-artifact-inventory.md
```

Support services and traits may live below `src/Service/Http/Vendor`, but the strict Cruding runtime contract applies only to FQCN services referenced by `config/platform/routes/**/*.yaml`.

