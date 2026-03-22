# Vendoring Wave 54 — DI wiring foundation

## Scope

Closed the next real Symfony mini-stack gap after kernel/bootstrap creation: interface-based constructor dependencies and repository/service autowire contracts were still mostly implicit and in one case pointed to a missing concrete repository.

## Added

- `src/Repository/VendorApiKeyRepository.php`
- `tests/Unit/Infrastructure/ServiceWiringContractTest.php`
- `tests/bin/di-smoke.php`

## Changed

- `config/services.yaml`
  - excluded non-service trees from broad `App\:` service discovery
  - added explicit aliases for repository/service interfaces used by commands and controllers
- `composer.json`
  - added `test:di`
  - extended `quality`
- `tests/Unit/Infrastructure/KernelConfigurationContractTest.php`
- `tests/Unit/Repository/DoctrineRepositoryContractTest.php`
- `tests/bin/smoke.php`

## Outcome

The component now declares an explicit DI/autowire contract for its interface-based services and repositories instead of relying on implicit or missing container resolution.
