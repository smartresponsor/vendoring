# Vendoring DependencyInjection Configuration Prefix Audit

## Scope

Wave Z cleans up the remaining non-prefixed component class under `src/DependencyInjection`.

## Finding

`src/DependencyInjection/Configuration.php` declared a generic `Configuration` class. That is a common Symfony bundle convention, but inside this repository it was the only active source class under `src` without the component prefix, while the ecosystem canon expects component-owned classes to carry the `Vendor` prefix unless they are Symfony application scaffolding such as `Kernel`.

## Change

- `Configuration` -> `VendorConfiguration`
- `src/DependencyInjection/Configuration.php` -> `src/DependencyInjection/VendorConfiguration.php`
- `VendoringExtension` now instantiates `VendorConfiguration`.
- `config/component/component.yaml` now points to `App\\Vendoring\\DependencyInjection\\VendorConfiguration`.

## Boundary

`src/Kernel.php` is intentionally not renamed in this wave because it is Symfony application bootstrap scaffolding, not a component business/service class.
