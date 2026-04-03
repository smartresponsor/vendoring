# Phase 70 — Canary Rollout Wiring

## Purpose

This phase wires existing rollout, probe, monitoring, and rollback seams into one operator-facing canary rollout contract.

The goal is to answer one practical question for a given feature flag and tenant/vendor scope:

- is the flag enabled for this cohort,
- are required probes present,
- is the release manifest green enough,
- should operators proceed, hold, or rollback,
- and what is the recommended next rollout action.

## New public contract

### `CanaryRolloutCoordinatorInterface`

Returns one stable report containing:
- feature-flag decision
- release manifest
- rollback decision
- canary verdict

### Operator HTTP endpoint

`GET /api/vendor-monitoring/canary-rollout?flagName=transaction_canary&tenantId=tenant-1&vendorId=42&windowSeconds=900`

### CLI entrypoint

`php bin/console app:vendor:canary-rollout --flag=transaction_canary --tenantId=tenant-1 --vendorId=42 --format=json`

## Decision model

- disabled flag → `disabled`
- missing required probes → `hold`
- rollback manifest decision = `rollback` → `rollback`
- rollback manifest decision = `hold` → `hold`
- green vendor canary → `proceed` + suggest tenant expansion
- green tenant canary → `proceed` + suggest global expansion
- green global rollout → `stable`

## Recommended actions

- `keep_flag_disabled`
- `keep_current_canary_scope`
- `disable_flag_for_current_cohort`
- `expand_canary_scope`
- `keep_global_rollout`

## Non-goals

This phase does not yet implement:
- percentage rollouts
- distributed flag storage
- automatic probe execution
- automatic rollback execution

It wires current seams into one explicit operator decision layer.
