# Vendoring Production Hardening Roadmap

Status: canonical  
Owner: Vendoring  
Lifecycle: active  
Scope: post-RC hardening within the Vendoring responsibility boundary

## Purpose

This document is the canonical execution roadmap for strengthening Vendoring after the current release-candidate baseline. It supersedes ad hoc planning notes that are not explicitly marked canonical.

Vendoring owns vendor onboarding, profile, readiness, ownership, capability, operational metadata, settlement prerequisites, statement preparation, transaction intake, payout readiness, and related runtime evidence.

Vendoring does not own order orchestration, buyer payment execution, shipping, taxation, legal-signature workflow, customer profile ownership, or external KYC-provider internals.

## Current verified baseline

- Symfony 8 and PHP 8.4 package surface;
- canonical `App\\Vendoring\\` namespace;
- zero-controller and zero-component-route boundary;
- Cruding-owned runtime dispatch;
- Objecting-owned reusable system-field packs;
- service and service-interface separation;
- Doctrine persistence tests;
- structured logging and correlation identifiers;
- metrics export, monitoring snapshots, and alert evaluation;
- RBAC, fault-tolerance, canary, and rollback contracts;
- generated OpenAPI artifacts and Nelmio integration;
- phpDocumentor integration;
- grouped RC quality lanes.

## Verified technical debt

1. PHPUnit line, method, and branch coverage is not enforced.
2. Full integration execution is not yet fully deterministic.
3. Semantic PHPDoc coverage is not machine-enforced for every production symbol.
4. Historical docs, active canon, README, route maps, Composer scripts, and generated API docs can drift.
5. Route-map to OpenAPI parity is not universal.
6. SOLID conformance is not fully measured.
7. Logging event naming, severity, redaction, duplication, and schema versioning are not yet one executable policy.
8. Every production class is not yet classified by reachability and runtime status.

## M4 — Technical Debt Closure

Status: planned  
Priority: first

### Scope

- isolate SQLite and temporary state per test process;
- guarantee subprocess teardown and timeout diagnostics;
- make unit, integration, functional, and smoke lanes independently deterministic;
- add PCOV or Xdebug coverage lane;
- generate Clover, Cobertura, HTML, and text coverage reports;
- establish baseline thresholds;
- create machine-readable class reachability inventory;
- remove tracked `.phpunit.cache` and other runtime artifacts;
- reconcile architecture backlog with actual implementation.

### Initial coverage targets

- line coverage: at least 85%;
- method coverage: at least 85%;
- branch coverage: at least 75%;
- policy, authorization, idempotency, payout, and rollback decisions: 100% decision coverage.

### Reachability statuses

- `active`;
- `internal`;
- `extension_point`;
- `deprecated`;
- `quarantined`.

### Exit criteria

- all test lanes terminate deterministically;
- coverage reports and thresholds are CI-enforced;
- every production class has a reachability status, consumer, test, and documentation source;
- no tracked runtime cache remains;
- architecture backlog matches actual code.

## M5 — PHPDoc and Documentation Canon

Status: planned

### Requirements

Every class, interface, trait, enum, and meaningful public method must document responsibility, ownership boundary, collaborators, normalization, outputs, null semantics, side effects, exceptions, persistence, idempotency, concurrency, security assumptions, units, and identifier meaning.

Reduce weak `array<string, mixed>` boundaries through readonly DTOs, projections, value objects, precise PHPStan shapes, enums, and typed collections.

Canonical documentation hierarchy:

- `docs/architecture/` — active architecture and roadmaps;
- `docs/api/` — public API contracts;
- `docs/operation/` — runtime and incident guidance;
- `docs/release/` — release evidence;
- `docs/internal/` — active implementation canon;
- `docs/internal/history/` — superseded material.

CI must reject broken links, conflicting canon, malformed encoding, unresolved placeholders, retired-route references, README/Composer mismatch, route-map/OpenAPI mismatch, outdated namespaces, undocumented production symbols, and stale generated docs.

### Exit criteria

- 100% production classes have semantic class documentation;
- 100% public methods have explicit or inherited contracts;
- 100% interfaces define substitution and failure semantics;
- 100% DTO/value-object fields document invariants and nullability;
- phpDocumentor generation and documentation drift gates are mandatory.

## M6 — OpenAPI and Nelmio Completion

Status: planned

For every active route record route key, HTTP method, URI, runtime service, input DTO/Form, validation, authentication, capability, response schema, errors, correlation headers, rate-limit headers, OpenAPI operation ID, and behavioral test.

Maintain one canonical error catalog containing error code, HTTP status, consumer-safe message, consumer action, retryability, log severity, and metric name.

### Exit criteria

- every active route has an OpenAPI operation;
- every documented operation maps to an active route;
- every runtime error is documented and tested;
- generated OpenAPI is reproducible and CI-enforced;
- versioning and deprecation are executable contracts.

## M7 — SOLID and Boundary Enforcement

Status: planned

- entrypoints depend on interfaces or approved framework contracts;
- orchestration services do not instantiate collaborators directly;
- environment, filesystem, time, randomness, and transport use adapters where determinism matters;
- policies perform no I/O;
- repositories contain no business orchestration;
- DTOs and value objects do not depend on container or Doctrine runtime;
- services combining normalization, authorization, persistence, calculation, rendering, or response formatting are reviewed for SRP;
- every interface has a public-contract, adapter, repository, policy, entrypoint, test-seam, or integration purpose.

### Exit criteria

- no unapproved concrete dependency in entrypoints;
- no service locator or hidden static mutable state;
- every interface has explicit boundary value;
- every SRP violation is split or formally justified;
- zero-controller and zero-route boundaries remain enforced.

## M8 — Logging and Observability Canon

Status: planned

Use stable event names such as `vendor.transaction.create.started`, `vendor.transaction.create.succeeded`, `vendor.transaction.create.rejected`, `vendor.payout.process.failed`, `vendor.statement.delivery.skipped`, and `vendor.authorization.denied`.

Each event defines severity, required and forbidden context, schema version, metric, and alert eligibility.

Logs must not expose API keys, bearer tokens, complete payout account identifiers, unapproved email addresses, raw request bodies, secrets, or internal stack traces in consumer responses.

Policy/domain layers express semantic failure, the orchestration boundary logs once, and the HTTP response layer does not duplicate it.

Critical metrics cover attempts, success, validation rejection, authorization rejection, conflict, provider failure, latency, and breaker state with bounded-cardinality dimensions.

### Exit criteria

- executable event catalog;
- redaction and duplicate-log tests;
- metrics completeness gate;
- no-op tracing adapter and propagation contract;
- optional OTLP export without business-service changes.

