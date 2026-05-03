# Vendoring Wave AL — Test projection residual sync

## Scope

This wave syncs active tests and smoke checks with the already-canonical source tree after the projection and DependencyInjection naming cleanup.

## Changes

- Replaced stale `Vendor*View` test references with canonical `Vendor*Projection` classes.
- Updated entity smoke coverage from stale `App\Vendoring\Entity\Vendor\VendorEntity\*` namespaces to `App\Vendoring\Entity\Vendor\*`.
- Updated Symfony mini-stack smoke coverage to expect `src/DependencyInjection/VendorConfiguration.php` instead of retired `Configuration.php`.

## Intentionally unchanged

- Historical audit/release docs may still mention retired names as history.
- `tools/vendoring-service-naming-audit.php` keeps retired names in its explicit forbidden-name map.
- `VendorEntity` remains valid when it refers to the PHP/Doctrine entity class.

## Verification

- `php -l` passed for all changed PHP files.
- Active `tests/`, `src/`, `config/`, and `tools/` no longer contain `Vendor*View` PHP references outside intentional audit/forbidden-name wording.
