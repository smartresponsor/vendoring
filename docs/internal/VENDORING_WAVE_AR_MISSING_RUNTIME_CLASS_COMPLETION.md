# Vendoring Wave AR — missing runtime class completion

## Purpose

Close active production-level class-reference gaps discovered after the final closure readiness pass.

## Findings

The structural and namespace reports were green, but a direct active class-reference scan found concrete `src/` services importing catalog merchandising and catalog syndication event/entity classes that were not present in the repository slice.

## Changes

- Added missing catalog merchandising entities under `src/Entity/Vendor` with `vendor_*` Doctrine table names.
- Added missing catalog review/change-request entities used by the review assignment service and repositories.
- Added missing catalog destination media and syndication payload event marker interfaces.
- Added matching immutable event classes extending the existing `VendorAbstractPayloadEvent`.
- Synced stale active smoke/config references from old Order placeholder examples to current Vendoring core service aliases.
- Synced DependencyInjection tests from retired `Configuration` to `VendorConfiguration`.
- Synced the stale observability chain collector test to `VendorMetricCollectorService`.

## Scope

This wave is intentionally production-safe: it completes classes already referenced by active services instead of introducing a new architecture layer.
