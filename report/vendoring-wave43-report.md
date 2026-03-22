# Vendoring wave 43 report

## Applied fixes
- Flattened forbidden `src/*/Vendor/*` structural branches across command, controller, DTO, event, repository, repository interface, service, service interface, entity interface, and value object layers.
- Preserved the only allowed domain exception: `src/Entity/Vendor/...`.
- Updated PHP namespaces and cross-layer imports to match the new canonical locations.
- Updated vendor transaction route/service wiring to the new class namespaces.
- Tightened runtime requirement in `composer.json` from mixed `^8.2 || ^8.3 || ^8.4` to `^8.4`.
- Added a minimal `tests/` tree so `autoload-dev` no longer points to a missing directory.

## Verification
- PHP syntax lint completed successfully for all files under `src/` and `tests/` after the refactor.
- Remaining `Vendor` directories under `src/` are limited to `src/Entity/Vendor/...` only.
