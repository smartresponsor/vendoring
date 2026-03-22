# Vendoring Wave 70 — Partial Uniqueness Source of Truth

## Summary
The `VendorTransaction` ORM metadata no longer pretends that transaction idempotency for `(vendor_id, order_id, project_id)` is a plain three-column unique constraint.

## Reason
The active SQL schema already uses split partial unique indexes to preserve correct semantics when `project_id` is `NULL`.
A plain ORM-level three-column unique declaration is misleading because nullable unique semantics differ from the actual migration-backed contract.

## Changes
- Removed misleading `uniqueConstraints` metadata from `src/Entity/Vendor/VendorTransaction.php`
- Added unit contract test for partial uniqueness source-of-truth
- Added smoke check for migration-backed uniqueness contract
- Extended Composer quality pipeline with `test:transaction-uniqueness-contract`

## Result
The repository now treats SQL migrations as the canonical source of truth for `project_id = NULL` and `project_id IS NOT NULL` duplicate guards.
