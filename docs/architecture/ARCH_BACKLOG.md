# Vendoring Architecture Backlog

## Purpose

This backlog translates the architecture review into concrete work items for the current repository shape:

- Symfony-oriented modular monolith
- strongest runtime contour around vendor transactions
- supporting statement, payout, ownership/security, and category/catalog slices
- release-candidate quality gates already in place

The goal is not to turn Vendoring into a distributed platform prematurely. The goal is to close the highest-value gaps in operational maturity, security posture, contract evolution, and architectural consistency.

## Current architectural position

### What is already strong

- Clear modular-monolith shape rather than accidental service sprawl
- Strong contract and release-candidate gates
- Good persistence parity discipline around the `VendorTransaction` vertical slice
- Stronger-than-average runtime proofs for a component at RC hardening stage
- Explicit release documentation and evidence-pack generation

### What is currently underdeveloped

- Production observability beyond test-oriented metrics
- Correlation across controller, service, persistence, and event paths
- Formal API authentication and authorization model
- Rate limiting and explicit public idempotency contract
- API versioning and schema evolution policy
- Zero-downtime migration readiness as an operational process
- Domain-boundary enforcement between internal bounded contexts

### What is intentionally out of scope for now

The following are intentionally not first-order priorities for the current repository shape:

- microservices decomposition
- service mesh or sidecars
- GraphQL
- gRPC
- WebSockets or WebRTC
- sharding or partitioning
- leader election or consensus systems
- CDN or DNS-level architecture
- data lake, warehouse, vector database, batch platform, or streaming platform

These should remain explicitly out of scope until the modular monolith has exhausted its scaling envelope.

## Priority model

- `P0`: must exist to make the system operationally supportable
- `P1`: should exist to make the system safe to evolve
- `P2`: should exist before significant scale or team growth
- `P3`: documentation and architecture governance tasks that prevent future drift

## Jira-style backlog

### Epic A: Operational Hardening

#### ARCH-001 Structured logging foundation
Priority: `P0`

Description:
Introduce a consistent application logging model across HTTP, service, and persistence boundaries.

Acceptance criteria:

- Runtime flows emit structured logs with `level`, `message`, `route`, `vendor_id`, `transaction_id`, `error_code`, and `correlation_id`.
- Transaction, payout, and statement failures are logged once at the correct boundary.
- Logging is covered by at least one integration or contract test.

#### ARCH-002 Correlation ID propagation
Priority: `P0`

Description:
Add request-scoped correlation IDs across inbound HTTP, internal service orchestration, and emitted events.

Acceptance criteria:

- Incoming `X-Correlation-ID` is accepted; absent IDs are generated.
- Responses include the correlation ID.
- Correlation ID is present in logs and event payload metadata.

#### ARCH-003 Production metrics adapter
Priority: `P0`

Description:
Replace the in-memory metrics collector with a runtime-ready metrics abstraction.

Acceptance criteria:

- Existing metric calls are routed through a replaceable adapter.
- Metrics exist for transaction create/update, payout create/process, and statement send/failure.
- The adapter supports export to Prometheus, OpenTelemetry, or StatsD.

#### ARCH-004 Runtime monitoring contract
Priority: `P1`

Description:
Define what "healthy" means for the component in production.

Acceptance criteria:

- Health signals are documented for HTTP, database, migrations, and key flows.
- Synthetic checks are defined for transaction create/list/update.
- Alert thresholds are drafted for failures and latency regressions.

### Epic B: Security and Access Model

#### SEC-001 Canonical authentication model
Priority: `P0`

Description:
Formalize API authentication around vendor API keys and clarify unsupported modes.

Acceptance criteria:

- The authentication flow is documented end-to-end.
- All public endpoints have an explicit authentication expectation.
- Transitional wrappers are marked as legacy or removed.

#### SEC-002 Authorization and RBAC normalization
Priority: `P1`

Description:
Convert ad hoc role and permission behavior into a single permission model.

Acceptance criteria:

- A canonical roles-and-permissions list exists.
- Transactions, payouts, statements, ownership, and operator actions have documented access rules.
- Authorization checks are covered by behavioral tests.

#### SEC-003 Secrets management readiness
Priority: `P1`

Description:
Remove reliance on local defaults as the operational model.

Acceptance criteria:

- Secrets loading supports env- or vault-based production deployment.
- No production secret defaults remain in service-config assumptions.
- A rotation procedure is documented.

#### SEC-004 API rate limiting
Priority: `P0`

Description:
Protect write-heavy and abuse-prone endpoints.

Acceptance criteria:

- Write endpoints return `429` under configured abuse conditions.
- Limits can be configured per token and or per vendor.
- Rate-limit behavior is test-covered.

#### SEC-005 Idempotency contract
Priority: `P0`

Description:
Promote duplicate prevention into an explicit public API contract.

Acceptance criteria:

- Idempotency semantics are documented.
- Duplicate-create behavior is deterministic.
- Concurrency and replay tests exist.

### Epic C: Release and Architecture Governance

#### GOV-001 API versioning strategy
Priority: `P1`

Description:
Define how contracts evolve without uncontrolled breakage.

Acceptance criteria:

- A versioning strategy is selected and documented.
- Deprecation rules and compatibility windows are defined.
- Future-breaking changes have a migration pattern.

#### GOV-002 Schema evolution policy
Priority: `P1`

Description:
Standardize migration behavior for additive and breaking changes.

Acceptance criteria:

- Migration authoring rules are documented.
- Additive-safe and destructive migration patterns are separated.
- Contract tests reference policy expectations.

#### GOV-003 Zero-downtime migration playbook
Priority: `P1`

Description:
Turn migration parity into an operational rollout method.

Acceptance criteria:

- Deploy/migrate/cleanup sequence is documented.
- Dangerous one-step migrations are prohibited by policy.
- Rollback implications are defined.

#### GOV-004 Domain boundary enforcement
Priority: `P1`

Description:
Stabilize bounded contexts and reduce hidden coupling.

Acceptance criteria:

- A context map exists for transaction, payout, statement, security/ownership, category/catalog, and ops.
- Cross-context dependency rules are documented.
- At least one automated guard exists for forbidden dependency drift.

#### GOV-005 Category/catalog post-recovery cleanup
Priority: `P2`

Description:
Validate that the restored category/catalog layer is intentional rather than merely compilable.

Acceptance criteria:

- Each new interface, entity, and event has an owning use case.
- Redundant transitional abstractions are removed or marked temporary.
- Context-level tests prove the intended invariants.

#### GOV-006 Semantic smoke-test refactor
Priority: `P1`

Description:
Replace string-fragile source inspections with semantic contract assertions.

Acceptance criteria:

- Critical smoke tests assert behavior, metadata, or schema meaning.
- Source-text coupling is reduced in migration and entity checks.
- Refactored tests preserve release-candidate coverage.

## GitHub issue set

### Issue 1: Add structured logging across critical runtime flows

Why:
Current observability is metric-oriented and too weak for production incident analysis.

Acceptance criteria:

- Structured logger is introduced and wired into transaction, payout, and statement flows.
- Validation failures, downstream failures, and persistence failures produce distinct log events.
- Logs include `correlation_id`, `vendor_id`, and business error code when available.

### Issue 2: Implement correlation ID middleware and propagation

Why:
Current runtime slices do not provide end-to-end request correlation.

Acceptance criteria:

- HTTP requests accept or generate `X-Correlation-ID`.
- Responses echo the correlation ID.
- Correlation ID is available to services and attached to emitted domain or integration events.

### Issue 3: Replace `MetricEmitter` with a production-ready metrics adapter

Why:
Current metrics implementation is test-friendly but operationally inert.

Acceptance criteria:

- A metrics interface abstracts the current emitter.
- Existing instrumentation remains intact through the new abstraction.
- Export path to a real backend is documented and test-covered.

### Issue 4: Define and enforce API authentication model

Why:
Authentication exists in pieces but not as a finished architecture.

Acceptance criteria:

- Public API endpoints declare the required auth mode.
- Vendor API key is the canonical auth mechanism unless explicitly exempted.
- Legacy wrapper services are either documented as transitional or removed.

### Issue 5: Formalize RBAC and endpoint authorization

Why:
Roles and ownership exist, but access control is not yet centrally governed.

Acceptance criteria:

- A role-to-permission matrix exists.
- Controller and service authorization rules are explicit and test-covered.
- Operator flows and vendor-local flows are separated cleanly.

### Issue 6: Add rate limiting for public write endpoints

Why:
The component has no clear abuse control for API write paths.

Acceptance criteria:

- `POST` transaction and status-changing endpoints are rate limited.
- A `429` response contract is defined.
- Limits are configurable and tested.

### Issue 7: Promote idempotency to a first-class API contract

Why:
Duplicate prevention exists but is still partly implicit.

Acceptance criteria:

- Idempotency behavior is documented for transaction creation.
- Concurrency and replay scenarios are tested.
- API consumers have a stable duplicate and error contract.

### Issue 8: Document API versioning and deprecation strategy

Why:
Contracts exist, but evolution rules do not.

Acceptance criteria:

- A versioning approach is chosen and documented.
- Backward-compatibility expectations are explicit.
- A deprecation path exists for future contract changes.

### Issue 9: Create zero-downtime migration playbook

Why:
Migration correctness is tested, but rollout safety is not formalized.

Acceptance criteria:

- Additive-safe migration sequence is documented.
- Destructive changes require multi-step rollout guidance.
- Rollback implications are included.

### Issue 10: Refactor fragile smoke tests into semantic contract tests

Why:
Some current smoke checks are too tightly coupled to source text.

Acceptance criteria:

- Source-string assertions are replaced where feasible with semantic metadata, schema, or runtime assertions.
- RC coverage remains equivalent or stronger.
- Tests become less sensitive to harmless refactors.

### Issue 11: Publish bounded-context map and dependency rules

Why:
The repository is a modular monolith, but context boundaries are still soft.

Acceptance criteria:

- A context map is documented.
- Each context has owned entities, services, and contracts.
- Forbidden dependency directions are stated and partially enforced.

### Issue 12: Audit restored category/catalog layer for intentional architecture

Why:
This layer was recently repaired under release pressure and may still contain recovery-only structure.

Acceptance criteria:

- Every new symbol is traced to a real use case.
- Redundant abstractions are removed.
- Context-level tests and docs reflect the intended steady-state design.

## Three-iteration roadmap

### Iteration 1: Stabilize

Goal:
Make the component operationally diagnosable and safe enough for controlled production use.

Scope:

- structured logging
- correlation ID
- real metrics abstraction
- authentication model documentation
- rate limiting on write endpoints
- idempotency contract formalization

Exit criteria:

- Every critical transaction path is traceable through logs and metrics.
- Public write endpoints have abuse controls.
- Authentication and idempotency are explicit, not inferred.

### Iteration 2: Harden

Goal:
Reduce architectural ambiguity and make contract and migration evolution safe.

Scope:

- RBAC normalization
- API versioning strategy
- schema evolution policy
- zero-downtime migration playbook
- semantic refactor of smoke guards
- rollback strategy draft

Exit criteria:

- Contract changes have a documented evolution path.
- Migration changes have rollout rules.
- Access control is centralized and testable.
- RC and CI gates validate architecture more semantically than textually.

### Iteration 3: Scale

Goal:
Prepare the component for higher load and more frequent delivery without changing its monolith-first architecture.

Scope:

- query and index review for hot paths
- capacity assumptions and DB/runtime sizing
- async-offloading candidates
- synthetic and canary probes
- feature-flag and traffic-cohort strategy
- bounded-context enforcement and category/catalog cleanup

Exit criteria:

- Hot paths have performance budgets.
- Release rollout can be scoped and verified safely.
- Domain boundaries are more explicit and less coupled.
- The repository is clearly governed as a modular monolith, not a pseudo-distributed system.

## Execution order recommendation

Recommended order:

1. `ARCH-001` structured logging foundation
2. `ARCH-002` correlation ID propagation
3. `ARCH-003` production metrics adapter
4. `SEC-001` canonical authentication model
5. `SEC-004` API rate limiting
6. `SEC-005` idempotency contract
7. `SEC-002` authorization and RBAC normalization
8. `GOV-001` API versioning strategy
9. `GOV-002` schema evolution policy
10. `GOV-003` zero-downtime migration playbook
11. `GOV-006` semantic smoke-test refactor
12. `GOV-004` domain boundary enforcement
13. `GOV-005` category/catalog post-recovery cleanup

## Notes for maintainers

- This document is intentionally architecture-first rather than feature-first.
- Do not use this backlog to justify premature distributed-systems complexity.
- Prefer closing observability, security, contract-evolution, and boundary-enforcement gaps before introducing new platform-level technology.
