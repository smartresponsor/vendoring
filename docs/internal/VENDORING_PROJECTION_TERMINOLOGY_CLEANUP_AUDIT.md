# Vendoring Projection Terminology Cleanup Audit

## Scope

This wave removes remaining `View` terminology from active projection-facing code and current canon documentation after the payload and builder classes were renamed to `Projection`.

## Findings

- Active PHP classes under `src/Projection` already use the `Vendor*Projection` suffix.
- Several projection-builder services still used local variable names such as `$ownershipView`, which no longer matched the returned projection payloads.
- Current machine-readable docs still described `src/Projection/Vendor/Vendor*View.php` as canonical.
- Older PHPDoc guide snippets still referenced deleted `Vendor*View` classes.

## Changes

- Renamed remaining local variables from `*View` to `*Projection` in projection-builder services.
- Updated current structure/naming canon documents to require `Vendor*Projection.php`.
- Updated runtime canon docs to name `VendorOwnershipProjection` and `VendorSecurityStateProjection`.
- Updated old PHPDoc guide examples so generated comments do not reintroduce deleted `Vendor*View` symbols.

## Out of scope

Historical patch manifests may still mention old deleted file names when documenting what a previous wave removed. Those references are historical audit evidence, not current class contracts.
