# PHASE66 — Real Observability Backend Seam

## Purpose

Introduce a deployment-safe observability backend seam so that structured runtime logs and
metrics are no longer limited to in-memory inspection or PHP error-log output.

## What was added

- `ObservabilityRecordExporterInterface`
- `FileObservabilityRecordExporter`
- runtime log export stream: `runtime_logs.ndjson`
- runtime metric export stream: `runtime_metrics.ndjson`
- DI wiring via `%vendoring_observability_dir%`
- semantic smoke: `test:observability-backend`

## Runtime layering

Structured observability now has three layers:

1. in-memory snapshots for tests and inspection
2. file-backed NDJSON export under `var/observability`
3. existing PHP error-log emission for non-test runtime environments

## Why this matters

This phase creates a real backend seam that can later be replaced or complemented by:
- Prometheus adapters
- OpenTelemetry exporters
- StatsD/UDP sinks
- external log shippers

without changing application-level logger and metric call sites.

## Current export paths

- `%kernel.project_dir%/var/observability/runtime_logs.ndjson`
- `%kernel.project_dir%/var/observability/runtime_metrics.ndjson`

## Non-goals

This phase does **not** yet provide:
- remote dashboards
- alerting
- distributed tracing spans
- Prometheus scraping
- OpenTelemetry collectors

## Acceptance baseline

The phase is complete when:
- runtime logger exports structured NDJSON records
- runtime metric collector exports structured NDJSON records
- DI wiring exposes the exporter as a canonical observability backend seam
- semantic smoke confirms both streams are created and populated
