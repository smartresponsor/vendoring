# Vendoring Wave 66 — Transaction Error Surface

## Scope
Stabilize transaction error codes so HTTP responses do not leak raw exception messages.

## Changes
- Added `App\ValueObject\VendorTransactionErrorCode` as the canonical catalog of transaction error codes.
- Switched transaction amount policy to canonical underscore-style error codes.
- Switched `VendorTransactionManager` to canonical codes for required identity checks, duplicate transaction guard, and invalid status transition.
- Hardened `VendorTransactionController` so it maps unknown validation messages to `transaction_validation_error` instead of returning raw exception text.
- Added smoke coverage for stable transaction error surface.

## Result
Transaction create/update endpoints now return a bounded and predictable error-code surface.
