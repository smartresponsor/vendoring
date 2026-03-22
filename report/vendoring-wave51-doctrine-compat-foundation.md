# Vendoring Wave 51 — Doctrine & entity-service compatibility foundation

## What was fixed
- Declared Doctrine runtime packages in `composer.json` because the current codebase already uses:
  - `Doctrine\\DBAL\\Connection`
  - `Doctrine\\ORM\\EntityManagerInterface`
  - `Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository`
- Added `test:compat` composer script for the entity/service compatibility slice.
- Added `VendorEntityServiceCompatibilityTest` to lock the reflection-backed property contract currently assumed by:
  - `VendorProfileService`
  - `VendorBillingService`
  - `VendorMediaService`
  - `VendorDocumentService`
- Extended smoke/configuration checks so runtime package declarations now cover both Symfony and Doctrine.

## Why this wave matters
Wave 50 restored the missing entity classes. This wave closes the next factual gap:
services and repositories already depend on Doctrine runtime packages, but `composer.json` did not declare them.

It also locks the current reflection-based mutation contract so future entity cleanups do not silently break the service layer.

## Result
The component is more reproducible as a Symfony/Doctrine-oriented application component, and the newly restored entity layer is now guarded against service-contract drift.
