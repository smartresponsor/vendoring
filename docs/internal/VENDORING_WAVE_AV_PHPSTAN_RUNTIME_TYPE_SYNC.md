# Vendoring Wave AV — PHPStan runtime type sync

## Scope

Wave AV is based on the user-provided current slice `VendoringSat(1).zip` and the PHPStan output supplied with that slice.

## Fixed categories

- Typed `VendorOwnershipMutationController` mutation callbacks with `VendorEntity` instead of generic `object`.
- Replaced the invalid `Symfony\Component\Mime\VendorEmailValueObject` usage with Symfony's `Email` message class.
- Corrected ownership projection repository count criteria typing for Doctrine object references.
- Normalized nullable commission status before passing it into `VendorCommissionEntity::updateConfiguration()`.
- Removed always-true Doctrine repository `instanceof` checks caused by PHPDoc-certain repository result types.
- Synced stale tests/support references from legacy entity namespaces/classes to canonical `App\Vendoring\Entity\Vendor\*Entity` classes.
- Updated observability tests/smokes to pass the required `VendorAppEnvResolverService` constructor dependency.
- Marked Doctrine entity persistence-only property diagnostics as PHPStan policy ignores for `src/Entity/*`, without deleting Doctrine fields.

## Deferred

The repository payout tests still model older DBAL-level behavior and should be converted in a follow-up runtime/PHPUnit wave if PHPUnit exposes behavioral drift. This wave focuses on the PHPStan categories visible in the supplied log and avoids destructive test rewrites.
