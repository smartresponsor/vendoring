# Phase 63 — Feature Flags and Cohort Rollout Seam

## Purpose

This phase introduces a deterministic rollout seam for risky runtime changes.

The seam is intentionally simple and modular-monolith friendly:
- resolve a canonical rollout cohort from tenant/vendor identity
- evaluate feature flags against either a global default or an explicit cohort allow-list

## Added public contracts

### `TrafficCohortResolverInterface`
Resolves a stable cohort identifier from tenant/vendor scope.

Canonical order:
1. `vendor:<vendorId>`
2. `tenant:<tenantId>`
3. `global`

### `FeatureFlagServiceInterface`
Evaluates whether a named feature flag is enabled for a resolved cohort.

Stable explanation payload:

```php
array{
  flag: string,
  enabled: bool,
  cohort: string,
  reason: string
}
```

## Current configuration model

Feature flags are represented as configuration arrays keyed by flag name.

Supported fields:
- `enabled: bool` — global default
- `cohorts: list<string>` — optional allow-list of explicit rollout cohorts

Example:

```php
[
  'new_operator_surface' => ['enabled' => true],
  'statement_canary' => [
    'enabled' => false,
    'cohorts' => ['tenant:tenant-1', 'vendor:42'],
  ],
]
```

## Why this seam matters

This closes the architectural gap between:
- synthetic probes
- canary validation
- controlled rollout by tenant/vendor
- future feature-flag guarded runtime changes

## Non-goals

This phase does **not** yet add:
- database-backed flag storage
- percentage rollouts
- operator UI for flag editing
- distributed flag propagation

## Acceptance baseline

- a canonical cohort can be resolved deterministically
- undefined flags fail closed
- globally enabled flags are explicit
- cohort-specific flags are explainable and test-covered
