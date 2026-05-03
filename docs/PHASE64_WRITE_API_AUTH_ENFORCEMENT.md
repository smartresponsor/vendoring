# Phase 64 — Write API authentication enforcement

## Purpose

This phase turns machine-access authentication from a passive capability into an enforced runtime contract on public write endpoints.

## Scope

Current enforcement applies to:
- `POST /api/vendor-transactions`
- `POST /api/vendor-transactions/vendor/{vendorId}/{id}/status`

## Contract

### Missing Authorization header
- status: `401`
- payload error: `authentication_required`
- headers:
  - `WWW-Authenticate: Bearer`
  - `X-Auth-Required-Permission: write:transactions`

### Invalid token
- status: `401`
- payload error: `invalid_api_token`

### Under-scoped token
- status: `403`
- payload error: `permission_denied`
- required permission: `write:transactions`

### Authorized token
- request proceeds into normal validation, rate limiting, idempotency, and domain logic

## Implementation notes

- `VendorApiKeyService` now validates authorization headers directly
- runtime harness provisions mapped Vendor and Vendor API key Doctrine entities (`VendorEntity`, `VendorApiKeyEntity`) for semantic auth tests
- the current enforcement is permission-based and does not yet bind external `vendorId` strings to vendor entity ownership

## Non-goals

This phase does not yet provide:
- human authentication
- SSO
- full per-endpoint authorization matrix
- cluster-safe token analytics
- vendor-entity to transaction-vendorId ownership binding

## Acceptance baseline

- public write endpoints reject missing auth headers
- public write endpoints reject invalid tokens
- public write endpoints reject under-scoped tokens
- authenticated write requests continue to pass through existing validation and rate-limiting paths
