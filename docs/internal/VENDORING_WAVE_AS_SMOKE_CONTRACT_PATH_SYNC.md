# Vendoring Wave AS — Smoke Contract Path Sync

## Purpose

Wave AS closes active smoke/contract drift discovered after Wave AR. The production source tree was already structurally aligned, but several smoke scripts and unit contract tests still checked retired paths and old alias locations.

## Scope

This wave updates active validation surfaces only:

- `tests/bin/*` smoke scripts;
- unit infrastructure contract tests;
- `.gitignore` runtime-analysis ignores;
- `tools/smoke/vendor-doctrine-mapping-smoke.php`.

No production service/entity/controller code is changed.

## Canonical syncs

- Transaction entity path is now `src/Entity/Vendor/VendorTransactionEntity.php`.
- Transaction entity interface path is now `src/EntityInterface/Vendor/VendorTransactionEntityInterface.php`.
- Transaction lifecycle service path is now `src/Service/Transaction/VendorTransactionLifecycleService.php`.
- Payout service/repository paths now use `Payout`/`Vendor`, not retired `VendorPayoutEntity` folders.
- App namespace repository smoke now checks current `VendorCoreServiceInterface` aliasing, not obsolete `OrderPayment*` placeholders.
- Interface alias smoke now checks current mirrored interface folders.
- DI smoke now checks `RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface`.
- Root/runtime smoke now accepts local `.php-cs-fixer.cache` and protocol-analysis artifacts only when they are ignored by git.

## Validation evidence

The following direct non-vendor smokes were run after the patch and passed:

- `php tests/bin/app-namespace-repository-smoke.php`
- `php tests/bin/di-smoke.php`
- `php tests/bin/interface-alias-smoke.php`
- `php tests/bin/payout-service-smoke.php`
- `php tests/bin/repository-contract-smoke.php`
- `php tests/bin/root-runtime-artifact-smoke.php`
- `php tests/bin/root-structure-smoke.php`
- `php tests/bin/transaction-doctrine-smoke.php`
- `php tests/bin/transaction-error-surface-smoke.php`
- `php tests/bin/transaction-idempotency-smoke.php`
- `php tests/bin/transaction-identity-smoke.php`
- `php tests/bin/transaction-mapping-parity-smoke.php`
- `php tests/bin/transaction-schema-parity-smoke.php`
- `php tests/bin/transaction-status-persistence-smoke.php`
- `php tests/bin/transaction-uniqueness-contract-smoke.php`

Core structure/canon checks were also run and remained green:

- `php tools/vendoring-structure-scan.php --strict`
- `php tools/vendoring-psr4-scan.php`
- `php tools/vendoring-service-naming-audit.php`
- `node tools/canon/vendor-canon-scan.mjs`
- `php tools/canon/vendor-scan.php`
- `php tools/vendoring-missing-class-triage.php --limit=2000`

## Residual notes

Vendor-backed runtime scripts that require `vendor/autoload.php` were not executed in the sandbox because dependencies are not installed here. They should be executed locally after applying the patch and running Composer install/update as appropriate.
