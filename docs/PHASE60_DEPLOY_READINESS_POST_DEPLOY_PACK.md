# Phase 60 — Deploy Readiness and Post-Deploy Verification Pack

## Purpose

This phase adds a deterministic post-deploy verification pack that can be executed after local activation, staging deployment, or release-candidate rollout.

The pack is intentionally narrow: it verifies that the repository exposes the core transaction corridor and the required documentation evidence bundle without depending on external infrastructure.

## Canonical entrypoint

```bash
composer test:post-deploy-verification
```

## Verification bundle

The verification pack asserts three groups of readiness conditions.

### 1. Documentation evidence

The following documents must be present:
- `docs/release/RC_BASELINE.md`
- `docs/release/RC_RUNTIME_SURFACES.md`
- `docs/release/RC_OPERATOR_SURFACE.md`
- `docs/PHASE59_SYNTHETIC_RUNTIME_PROBES.md`

### 2. Runtime transaction corridor

When `pdo_sqlite` is available, the pack boots an isolated kernel runtime and verifies:
- transaction create returns `201`
- transaction list returns `200`
- transaction status update returns `200`
- created transaction is visible in list results
- status update yields `authorized`

### 3. Operational headers

The pack also verifies that the runtime exposes the operational HTTP contracts already formalized in earlier phases:
- `X-API-Version`
- `X-Correlation-ID`
- rate-limit headers on mutation responses

## Environment rule

If `pdo_sqlite` is not available, the pack exits with a clear skip message instead of producing a false failure.

## Why this matters

This pack gives the repository a practical post-deploy verification path that sits between unit/integration tests and full live-environment monitoring.

It is suitable for:
- local release rehearsal
- staging validation
- post-merge confidence checks
- release-candidate evidence gathering

## Non-goals

This phase does not:
- verify external mail delivery
- verify third-party integrations
- verify production database migration safety
- replace synthetic probes, canaries, or full environment monitoring

## Acceptance baseline

This phase is complete when:
- the composer entrypoint exists
- the verification pack validates docs + runtime corridor + operational headers
- the pack produces deterministic success or explicit environment skip
