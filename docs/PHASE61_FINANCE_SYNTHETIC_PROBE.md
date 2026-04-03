# Phase 61 — Finance Synthetic Probe

## Purpose

This phase adds a synthetic runtime probe for finance-adjacent public surfaces so that post-deploy verification is not limited to the transaction corridor.

## Canonical entrypoint

```bash
composer test:finance-synthetic-probe
```

## Covered runtime corridor

The probe verifies the following sequence against an isolated kernel/runtime environment:

1. payout-account upsert via `POST /api/payouts/account`
2. vendor statement build via `GET /api/payouts/statements/{vendorId}`
3. vendor statement export via `GET /api/payouts/statements/{vendorId}/export`

## Contract expectations

- payout account upsert returns `200` with a `data` payload
- statement build returns `200` with a `data` payload
- statement export returns `200` with a `data.pdfBase64` payload
- a stable `X-Correlation-ID` is supplied across the whole probe

## Environment rule

If `pdo_sqlite` is not available, the probe exits with an explicit skip instead of producing a false red signal.

## Non-goals

This probe does not yet validate payout creation/processing thresholds or downstream transfer semantics.
Those remain covered by dedicated payout tests and future synthetic corridors.

## Acceptance baseline

The phase is complete when the repository contains:

- a dedicated finance synthetic probe script
- a composer entrypoint for invoking the probe
- phase documentation describing scope and expectations
