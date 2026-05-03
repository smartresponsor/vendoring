# Vendoring Wave AW — PHPStan 81 residual sync

## Scope

Wave AW targets the PHPStan residual batch reported after the Wave AV runtime type sync. The batch was reduced to focused issues in production typing, Symfony Mime usage, stale test entity names, repository test doubles, and runtime observability constructor drift.

## Production fixes

- Replaced the invalid `Symfony\Component\Mime\VendorEmailValueObject` with Symfony `Email` in the statement mailer.
- Typed ownership mutation callbacks with `VendorEntity` instead of generic `object`.
- Tightened ownership projection count criteria and repository class-string typing.
- Normalized nullable commission status before passing it to `VendorCommissionEntity::updateConfiguration()`.
- Removed an always-true category `instanceof` branch in ownership write flow.
- Added explicit `published()` accessors to catalog banner/html block entities used by active tests.

## Test/support fixes

- Replaced stale entity imports such as `App\Vendoring\Entity\VendorTransaction`, `Vendor`, `VendorApiKey`, `VendorUserAssignment`, `VendorBilling`, and catalog merch aliases with canonical `App\Vendoring\Entity\Vendor\*Entity` classes.
- Updated transaction support fakes and Doctrine-backed test repository to `VendorTransactionEntity`.
- Updated runtime observability tests/smokes for the current `VendorAppEnvResolverService` constructor dependency.
- Reworked payout repository unit tests to match the current Doctrine `EntityManagerInterface` repositories instead of legacy DBAL connection constructors.
- Updated ownership projection tests to provide an EntityManager double for relation counts.
- Guarded runtime smoke payload access before reading `relationCounts`.

## PHPStan policy

Doctrine entity `property.onlyWritten` and `property.unusedType` warnings are ignored for `src/Entity/*` because the entity model is intentionally Doctrine-oriented and should not be reshaped with artificial getters just to satisfy static analysis noise.
