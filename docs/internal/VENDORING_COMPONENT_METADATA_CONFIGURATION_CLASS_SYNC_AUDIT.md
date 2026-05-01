# Vendoring component metadata configuration-class sync audit

## Scope

Wave AH synchronizes component metadata after the DependencyInjection configuration class was renamed.

## Finding

`src/DependencyInjection/Configuration.php` was previously retired in favor of `VendorConfiguration.php`, but `config/component/component.yaml` still pointed at `App\Vendoring\DependencyInjection\Configuration`.

## Change

`configuration_class` now points at `App\Vendoring\DependencyInjection\VendorConfiguration`.

## Risk note

This is a metadata-only correction. Runtime extension code already instantiates `VendorConfiguration`, so this wave removes stale component metadata rather than changing behavior.
