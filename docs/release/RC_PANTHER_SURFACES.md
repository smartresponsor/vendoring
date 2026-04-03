# Vendoring RC Panther Surfaces

## Purpose

This document describes the browser-level (Panther) verification layer added at the RC stage.

Panther is used as a **release confidence layer**, not as a primary validation layer.
All core business correctness remains covered by unit, integration, and runtime tests.

## Execution mode

Panther tests run against an already booted application via:

```
PANTHER_EXTERNAL_BASE_URI=http://localhost:8000
```

This allows:
- testing real runtime wiring
- avoiding fixture coupling
- reusing the same suite across environments (local, staging, Kubernetes)

## Implemented RC scenarios

### 1. API documentation surface
- GET `/api/doc`
- verifies page renders
- verifies "Vendoring API" marker is present

### 2. Runtime status surface
- GET `/api/vendor-runtime-status/tenant/{tenantId}/vendor/{vendorId}`
- verifies JSON response
- verifies `data` payload exists

### 3. Release baseline surface
- GET `/api/vendor-release-baseline/tenant/{tenantId}/vendor/{vendorId}`
- verifies JSON response
- verifies baseline structure (`status`, `data`)

### 4. Vendor transaction flow
- POST create transaction
- GET list by vendor
- POST update status
- verifies end-to-end flow through HTTP layer

### 5. Duplicate transaction contract
- POST same payload twice
- verifies `409 duplicate_transaction`

## Design rules

- no heavy assertions duplicated from integration layer
- focus on runtime reachability and surface stability
- use random identifiers to avoid cross-test contamination
- keep tests deterministic and fast

## Result

This layer confirms that:
- HTTP surfaces are wired correctly
- contracts are visible through real runtime
- RC can be exercised externally as a system, not only internally as a component

