# Phase 68 — Monitoring and alerting layer

## Purpose

Introduce a first-class monitoring snapshot and alert evaluation layer on top of the existing
observability backend seam and fault-tolerance state.

## Implemented layer

- monitoring snapshot builder over exported NDJSON log and metric streams
- breaker-state aggregation over file-backed circuit breaker records
- probe-artifact readiness summary
- deterministic alert evaluator with warning/critical alerts
- operator HTTP endpoint: `/api/vendor-monitoring/overview`
- CLI entrypoint: `app:vendor:monitoring-snapshot`
- semantic smoke: `test:monitoring-alerting`

## Snapshot contract

The monitoring snapshot is read-side and side-effect free.
It reports:

- recent runtime log totals and error/warning counts
- unique recent routes and error codes
- recent metric totals and names
- breaker counts and open scopes
- presence of synthetic/post-deploy probe artifacts
- overall status: `ok` or `warn`

## Alert rules

Current rules are intentionally simple and deterministic:

- `runtime_error_spike`
- `outbound_circuit_open`
- `probe_artifacts_missing`
- `observability_metrics_empty`

## Non-goals

This phase does not yet provide:

- external dashboard provisioning
- remote paging integrations
- SLA/SLO management
- distributed monitoring storage

## Acceptance baseline

This phase is complete when:

- the monitoring snapshot can be rendered via service, HTTP, and CLI surfaces
- alerts are derived deterministically from the snapshot
- semantic smoke confirms error, breaker, and alert evaluation behavior
