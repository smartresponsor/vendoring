# Phase 62 — Payout processing synthetic probe

## Purpose

This phase adds a dedicated synthetic runtime probe for the payout processing corridor.

The goal is to verify that a post-deploy runtime can:
- create a payout,
- read it back in pending state,
- process it,
- read it back in processed state.

## Canonical entrypoint

```bash
composer test:payout-processing-synthetic-probe
```

## Covered corridor

1. `POST /api/payout/create`
2. `GET /api/payout/{payoutId}`
3. `POST /api/payout/process/{payoutId}`
4. `GET /api/payout/{payoutId}` after processing

## Contract expectations

- create returns `201`
- create payload contains `data.created = true`
- create payload contains a non-empty `payoutId`
- initial read returns `status = pending`
- process returns `200`
- process payload contains `data.processed = true`
- final read returns `status = processed`

## Correlation rule

The probe sends a stable `X-Correlation-ID` header so payout create/process actions remain traceable in runtime logs and metrics.

## Environment rule

The probe requires `pdo_sqlite` for isolated runtime execution.
When the extension is unavailable, the probe exits with a clear skip message instead of producing a false red signal.

## Non-goals

This probe does not validate:
- external payout provider delivery,
- asynchronous payout orchestration,
- canary cohort behavior,
- feature-flag routing.

## Acceptance baseline

Phase 62 is complete when:
- the synthetic probe runs through the payout processing corridor,
- the composer script is available,
- the probe behaves deterministically in both run and skip modes.
