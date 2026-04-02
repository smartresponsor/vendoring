# Panther Smoke Plan

## Purpose

This plan defines the first browser-level smoke layer for Vendoring.

Panther is used here as a release-candidate regression layer, not as the primary place for business logic validation.
Business logic remains covered by unit, integration, and runtime consistency tests.

## Initial mode

Use external-base execution first.

Why:
- browser flows should target a booted application
- this avoids coupling smoke tests to incomplete in-process fixture orchestration
- the same smoke suite can later run against ephemeral environments and Kubernetes namespaces

Canonical environment variable:
- `PANTHER_EXTERNAL_BASE_URI`

## First smoke targets

1. API documentation surface
2. Vendor runtime status endpoint
3. Vendor release baseline endpoint

## Initial success criteria

A smoke run is considered green when:
- the browser client can reach the configured base URI
- `/api/doc` responds successfully when enabled
- runtime status endpoint responds with a JSON payload containing `data`
- release baseline endpoint responds with a JSON payload containing `data`

## Future browser scenarios

After the first smoke layer is stable, extend to:
- vendor profile edit flow
- profile publish flow
- payout flow
- statement send flow
- runtime/admin page flow

## Rule

Do not move heavy business assertions into Panther when the same correctness can be validated faster and more deterministically at the integration layer.
