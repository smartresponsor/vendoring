# Phase 59 — Synthetic Runtime Probes

## Purpose

This phase adds a reproducible synthetic runtime probe for the highest-value transaction path.

The probe is intended for:
- local pre-release verification
- post-deploy smoke validation
- canary/runtime readiness checks

It is not intended to replace PHPUnit integration coverage.

## Implemented probe

### Script
- `tests/bin/runtime-synthetic-probe.php`

### Composer entrypoint
- `composer test:runtime-synthetic-probe`

## Probe flow

The probe boots a fresh kernel/runtime harness and validates the canonical transaction corridor:

1. create transaction
2. list transactions by vendor
3. update status to `authorized`

## Contract expectations

### Create
- HTTP `201`
- payload contains transaction id
- status equals `pending`

### List
- HTTP `200`
- created transaction is visible in vendor list payload

### Status update
- HTTP `200`
- payload status equals `authorized`

## Correlation rule

The probe sends a stable `X-Correlation-ID` so that runtime logging and metrics can correlate the synthetic request path.

## Environment rule

The probe requires `pdo_sqlite` because it boots an isolated sqlite runtime harness.
When `pdo_sqlite` is unavailable, the probe exits successfully with an explicit skip message instead of generating a false failure.

## Operational use

Recommended usage:
- before RC packaging
- immediately after deployment
- as a canary validation path in controlled rollout environments

## Non-goals

This phase does not yet introduce:
- remote health endpoints
- continuous probe scheduling
- environment-specific alerting
- tenant-specific cohort orchestration

## Acceptance baseline

Phase 59 is complete when:
- the synthetic probe is committed
- the composer entrypoint is available
- the probe verifies create/list/update transaction semantics
- the probe behaves deterministically in environments without `pdo_sqlite`
