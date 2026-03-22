# Vendoring Wave 69 — Transaction Schema Parity

## Scope
Strengthen ORM-to-SQL parity for `VendorTransaction` table-level metadata.

## Changes
- Added explicit Doctrine table index metadata for `idx_vendor_transaction_vendor_created`
- Added explicit Doctrine unique constraint metadata for vendor/order/project identity surface
- Added schema parity unit test
- Added schema parity smoke script
- Extended Composer quality pipeline with `test:transaction-schema-parity`

## Rationale
SQL migrations already declared ordering and uniqueness contracts for `vendor_transaction`, but entity metadata still omitted them. This wave makes the ORM surface explicit and test-guarded.
