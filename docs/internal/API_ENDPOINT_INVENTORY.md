# API Endpoint Inventory

## Vendor profile and ownership

- GET `/api/vendor/profile/{vendorId}` — Read vendor profile projection
- PATCH `/api/vendor/profile/{vendorId}` — Upsert vendor profile fields
- GET `/api/vendor/ownership/{vendorId}` — Read ownership projection

## Vendor runtime projections

- GET `/api/vendor/runtime/finance/{vendorId}` — Finance runtime readiness projection
- GET `/api/vendor/runtime/external/integration/{vendorId}` — External integration runtime projection
- GET `/api/vendor/runtime/statement/delivery/{vendorId}` — Statement delivery runtime projection

## Transactions

- POST `/api/vendor/transaction` — Create transaction (write-auth required)
- GET `/api/vendor/transaction/{vendorId}` — List transactions by vendor
- POST `/api/vendor/transaction/status/{vendorId}/{id}` — Update transaction status (write-auth required)

## Payout

- POST `/api/vendor/payout` — Request payout creation
- POST `/api/vendor/payout/process/{payoutId}` — Process payout
- GET `/api/vendor/payout/{payoutId}` — Read payout by id

## Statements

- GET `/api/vendor/{vendorId}/payout/statement` — Build statement payload
- GET `/api/vendor/{vendorId}/payout/statement/export` — Export statement PDF payload/artifact

## Operational and release endpoints

- GET `/api/vendor/runtime/status/{vendorId}` — Runtime status snapshot
- GET `/api/vendor/release/baseline/{vendorId}` — Release baseline surface
- GET `/api/vendor/monitoring/release/manifest` — Runtime release manifest
- GET `/api/vendor/monitoring/overview` — Monitoring alert snapshot
- GET `/api/vendor/monitoring/canary/rollout` — Canary rollout state

## Metrics and ledger

- GET `/api/vendor/metric/overview/{vendorId}` — Vendor metric overview
- GET `/api/vendor/metric/trend/{vendorId}s` — Vendor metric trends
- GET `/api/vendor/ledger/summary/{vendorId}` — Vendor ledger summary
