# Phase 67 — Fault Tolerance Runtime Seam

## Purpose

Introduce a real runtime fault-tolerance seam for outbound operations instead of leaving timeout, retry, and breaker behavior as implicit assumptions.

## Scope

This phase adds:
- canonical outbound operation policy lookup
- file-backed circuit breaker state
- runtime integration of breaker checks into statement mail send
- unit coverage and semantic smoke coverage

## Canonical outbound policy payload

```php
array{
  operation: string,
  timeoutSeconds: int,
  maxAttempts: int,
  retryable: bool,
  failureMode: string,
  breakerThreshold: int,
  cooldownSeconds: int
}
```

## Canonical breaker state payload

```php
array{
  operation: string,
  scopeKey: string,
  state: string,
  failureCount: int,
  threshold: int,
  cooldownSeconds: int,
  allowRequest: bool
}
```

## Statement mail behavior

`statement_mail_send` now:
- validates email first
- reads canonical outbound policy
- consults the circuit breaker per `tenantId:vendorId`
- short-circuits when breaker is open
- records breaker failures on transport exceptions
- resets breaker state on success

## Non-goals

This phase does **not** yet introduce:
- external breaker backend (Redis, DB, etc.)
- true transport-level timeout enforcement
- breaker integration for every outbound path
- generalized retry execution for retryable providers

## Acceptance baseline

- policy lookup is machine-readable
- breaker state is persisted across requests in the local runtime
- statement mail send is protected from repeated transport failures
- unit tests and semantic smoke confirm the contract
