# API Endpoint Inventory

## Vendor profile and ownership

- GET `/api/vendor-profile/vendor/{vendorId}` — Read vendor profile projection
- PATCH `/api/vendor-profile/vendor/{vendorId}` — Upsert vendor profile fields
- GET `/api/vendor-ownership/vendor/{vendorId}` — Read ownership projection

## Vendor runtime projections

- GET `/api/vendor/runtime/{vendorId}/finance` — Finance runtime readiness projection
- GET `/api/vendor/runtime/{vendorId}/external-integrations` — External integration runtime projection
- GET `/api/vendor/runtime/{vendorId}/statement-delivery` — Statement delivery runtime projection

## Transactions

- POST `/api/vendor-transactions` — Create transaction (write-auth required)
- GET `/api/vendor-transactions/vendor/{vendorId}` — List transactions by vendor
- POST `/api/vendor-transactions/vendor/{vendorId}/{id}/status` — Update transaction status (write-auth required)

## Payout

- POST `/api/payout/create` — Request payout creation
- POST `/api/payout/process/{payoutId}` — Process payout
- GET `/api/payout/{payoutId}` — Read payout by id

## Statements

- GET `/api/payouts/statements/{vendorId}` — Build statement payload
- GET `/api/payouts/statements/{vendorId}/export` — Export statement PDF payload/artifact

## Operational and release endpoints

- GET `/api/vendor-runtime-status/tenant/{tenantId}/vendor/{vendorId}` — Runtime status snapshot
- GET `/api/vendor-release-baseline/tenant/{tenantId}/vendor/{vendorId}` — Release baseline surface
- GET `/api/vendor-monitoring/release-manifest` — Runtime release manifest
- GET `/api/vendor-monitoring/overview` — Monitoring alert snapshot
- GET `/api/vendor-monitoring/canary-rollout` — Canary rollout state

## Metrics and ledger

- GET `/api/metrics/vendor/{vendorId}/overview` — Vendor metric overview
- GET `/api/metrics/vendor/{vendorId}/trends` — Vendor metric trends
- GET `/api/ledger/vendor/{vendorId}/summary` — Vendor ledger summary
