# Vendoring Wave 56 — entrypoint contracts

## Scope
- harden command/controller entrypoint constructor contracts
- bring `VendorTransactionController` to canonical Symfony controller shape
- add smoke/unit checks for entrypoint dependency discipline

## Changes
- `src/Controller/VendorTransactionController.php`
  - now extends `AbstractController`
  - now has class/action `#[Route]` attributes
  - now depends on `VendorTransactionRepositoryInterface` instead of direct `EntityManagerInterface`
  - added `listByVendor()` action for canonical read path
  - added request validation for required create payload fields
- `tests/Unit/Infrastructure/EntryPointConstructorContractTest.php`
  - verifies commands/controllers use interfaces or approved framework contracts in constructors
- `tests/bin/entrypoint-contract-smoke.php`
  - verifies `test:entrypoint` script and canonical `VendorTransactionController` shape
- `composer.json`
  - added `test:entrypoint`
  - extended `quality`
- `tests/bin/smoke.php`
  - requires `test:entrypoint`

## Key finding closed
Before this wave, `VendorTransactionController` was not aligned with the rest of the Symfony-oriented HTTP surface:
- no `AbstractController`
- no route attributes
- direct `EntityManagerInterface` in controller constructor
- no canonical list/read action

This wave closes that gap and adds guardrails so future entrypoints do not drift silently.
