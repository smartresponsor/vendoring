# Vendoring Wave AN — Active label residual sync

## Scope

This wave cleaned active smoke/runtime/test/tooling labels that still exposed `VendorEntity` as a business-facing word after the entity wording boundary had already been canonized.

## Changes

- Replaced synthetic runtime seed names that said `VendorEntity` with neutral `Vendor` names.
- Replaced user-facing smoke output text from `VendorEntity doctrine mapping smoke passed` to `Vendor doctrine mapping smoke passed`.
- Replaced a transaction schema smoke error message with `Vendor transaction SQL migrations ...` instead of `VendorEntity transaction SQL migrations ...`.
- Replaced test fixture display names such as `VendorEntity A` and `VendorEntity X` with `Vendor A` and `Vendor X`.
- Reworded git-history tooling comments and documentation title from `VendorEntity` to `Vendoring`.
- Clarified the active phase-64 auth note so `VendorEntity` and `VendorApiKeyEntity` remain explicitly class names, not business labels.

## Boundary

This wave intentionally does not rename PHP/Doctrine class names, type hints, imports, repository contracts, or entity relationship references. `VendorEntity` remains correct as a PHP/Doctrine class name under `App\\Vendoring\\Entity\\Vendor`.
