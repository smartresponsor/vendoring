# Vendoring Architecture Review — GitHub Issues V2

This document converts the current 120-point architecture review into a practical GitHub-Issues-oriented execution plan.

## P0 — Must before real production exposure

### ISSUE SEC-001 — Enforce canonical API-key authentication on public write endpoints
**Summary**
Turn machine authentication into an active runtime contract for all public write endpoints.

**Why**
Authentication seams exist, but enforcement is still weaker than rate limiting and contract documentation.

**Acceptance criteria**
- write endpoints require canonical machine credentials
- `401` and `403` behaviors are explicit and test-covered
- invalid, missing, and under-scoped credentials are covered by behavioral tests
- auth contract is reflected in public API docs

**Labels**
`security` `authentication` `api` `p0`

---

### ISSUE SEC-002 — Formalize and enforce RBAC authorization matrix
**Summary**
Make authorization decisions explicit, canonical, and uniformly enforced.

**Why**
Ownership and assignment exist, but the role/capability model still risks drift if not centralized.

**Acceptance criteria**
- canonical roles are documented
- role-to-capability matrix is implemented as a runtime contract
- controller/service enforcement uses the matrix consistently
- owner/operator/finance/viewer cases are test-covered

**Labels**
`security` `rbac` `authorization` `p0`

---

### ISSUE OBS-001 — Replace local metrics path with real observability backend
**Summary**
Move from local metrics collection to production-grade metric export.

**Why**
Logging/correlation/metric baselines exist, but no real observability backend or dashboard path exists yet.

**Acceptance criteria**
- Prometheus/Otel/StatsD-style backend is wired in
- transaction/payout/statement metrics are exported
- latency and error metrics are externally visible
- documentation explains the observability contract

**Labels**
`observability` `metrics` `operations` `p0`

---

### ISSUE REL-001 — Define unified fault-tolerance policy
**Summary**
Create one canonical runtime policy for validation, transient, downstream, and persistence failures.

**Why**
There are strong tests and partial seams, but no complete runtime rulebook for failure handling.

**Acceptance criteria**
- error classes are defined
- retry / fail-fast / compensating action guidance is documented
- policy is reflected in code where relevant
- major downstream paths follow the same rules

**Labels**
`reliability` `architecture` `runtime` `p0`

---

### ISSUE REL-003 — Add circuit breaker / bulkhead around outbound providers
**Summary**
Protect the main request path from mail/provider instability.

**Why**
Outbound policy seams exist, but actual provider isolation is still missing.

**Acceptance criteria**
- outbound providers are isolated behind breaker/bulkhead logic
- degraded mode is explicit and observable
- breaker state can be monitored
- provider outage does not collapse unrelated runtime behavior

**Labels**
`reliability` `integration` `outbound` `p0`

---

### ISSUE GOV-001 — Make API versioning a universal runtime contract
**Summary**
Turn versioning from documentation intent into a consistent runtime behavior.

**Why**
Versioning direction exists, but not every public response is yet aligned.

**Acceptance criteria**
- all public API responses expose one canonical versioning contract
- deprecation strategy is documented
- OpenAPI/docs match runtime headers and policy

**Labels**
`api` `versioning` `contracts` `p0`

---

### ISSUE GOV-002 — Enforce schema evolution policy
**Summary**
Make schema evolution an explicit engineering discipline.

**Why**
Migration correctness is good, but rollout-safe schema evolution is not yet uniformly enforced.

**Acceptance criteria**
- additive vs breaking schema rules are documented
- migration checklist exists
- deployment workflow aligns with schema policy

**Labels**
`database` `schema` `migrations` `p0`

---

### ISSUE GOV-003 — Formalize zero-downtime migration discipline
**Summary**
Turn migration-readiness into a deployment-safe procedure.

**Why**
Migration playbook direction exists, but operational enforcement is still weak.

**Acceptance criteria**
- deploy → migrate → cleanup sequence is documented
- destructive one-step migrations are explicitly forbidden
- rollback interactions are documented

**Labels**
`database` `deploy` `zero-downtime` `p0`

---

## P1 — Strong next wave

### ISSUE SEC-003 — Replace file-backed rate limiter with deployment-safe backend
**Summary**
Make rate limiting correct under multi-node deployment.

**Acceptance criteria**
- limiter state is shared and deployment-safe
- per-token / per-vendor / per-IP policy is explicit
- runtime headers remain stable
- tests cover cluster-safe semantics

**Labels**
`security` `rate-limit` `runtime` `p1`

---

### ISSUE API-002 — Normalize idempotency contract everywhere it applies
**Summary**
Generalize the strongest-slice idempotency semantics into a repository-wide contract where applicable.

**Acceptance criteria**
- duplicate/idempotency rules are documented
- normalization rules are explicit
- relevant endpoints expose consistent behavior
- tests cover negative and normalization paths

**Labels**
`api` `idempotency` `contracts` `p1`

---

### ISSUE PERF-001 — Build capacity and hot-path performance baseline
**Summary**
Establish factual capacity and performance expectations.

**Acceptance criteria**
- RPS/write/day assumptions documented
- hot endpoints identified
- query plans reviewed for strongest corridors
- initial perf budgets recorded

**Labels**
`performance` `capacity` `database` `p1`

---

### ISSUE ASYNC-001 — Create async offloading roadmap
**Summary**
Identify which sync flows should eventually move to async processing.

**Acceptance criteria**
- async candidates listed
- selection criteria documented
- first extraction target chosen

**Labels**
`architecture` `async` `roadmap` `p1`

---

### ISSUE TEST-002 — Add browser-level E2E coverage
**Summary**
Add browser-visible E2E coverage on top of probes and semantic smokes.

**Acceptance criteria**
- critical UI/API journeys are covered end-to-end
- operator/runtime surfaces are validated through browser-level tests
- E2E test lane is integrated into release or CI workflow

**Labels**
`testing` `e2e` `quality` `p1`

---

### ISSUE OPS-004 — Add monitoring dashboards
**Summary**
Make runtime health visible to operators.

**Acceptance criteria**
- dashboard exists for logs/metrics/probes
- core runtime corridors are visible
- release/readiness artifacts are operationally consumable

**Labels**
`operations` `monitoring` `observability` `p1`

---

### ISSUE OPS-005 — Add alerting based on errors and SLOs
**Summary**
Close the loop from metrics/logs to operator action.

**Acceptance criteria**
- alert conditions defined
- severe error spikes and failed critical probes trigger signals
- operator response playbook linked

**Labels**
`operations` `alerting` `sre` `p1`

---

## P2 — After RC stabilization

### ISSUE DEPLOY-002 — Wire cohorts + feature flags + probes into real canary rollout
**Summary**
Move from rollout seam to actual controlled rollout workflow.

**Acceptance criteria**
- feature flags and cohorts influence live rollout decisions
- synthetic probes can validate cohort-scoped rollouts
- rollback path exists without full redeploy

**Labels**
`deploy` `canary` `rollout` `p2`

---

### ISSUE DEPLOY-004 — Create rollback manifest and rollback procedure
**Summary**
Make rollback an explicit operational practice.

**Acceptance criteria**
- rollback checklist exists
- rollback artifacts/manifest are defined
- code/schema rollback interaction is documented

**Labels**
`deploy` `rollback` `operations` `p2`

---

### ISSUE SEC-004 — Introduce production secrets management
**Summary**
Move from current secret/config patterns to a production-grade secret-management path.

**Acceptance criteria**
- secret manager/vault path documented and integrated
- rotation path documented
- default inline secret use is eliminated for production modes

**Labels**
`security` `secrets` `operations` `p2`

---

### ISSUE SEC-005 — Define encryption posture
**Summary**
Make encryption requirements explicit for transport and sensitive data handling.

**Acceptance criteria**
- transport encryption assumptions documented
- at-rest / sensitive-field treatment documented
- gaps called out clearly where encryption is deferred

**Labels**
`security` `encryption` `governance` `p2`

---

### ISSUE SEC-006 — Produce threat model for public write API and operator surfaces
**Summary**
Document adversaries, abuse cases, and key mitigations.

**Acceptance criteria**
- threat model document exists
- public write API and operator surfaces are covered
- mitigations map to concrete engineering work

**Labels**
`security` `threat-modeling` `governance` `p2`

---

### ISSUE DOCS-001 — Replace fallback phpDocumentor path with rich upstream generator
**Summary**
Upgrade from fallback generation to full rich API documentation generation.

**Acceptance criteria**
- upstream phpDocumentor tooling is wired in
- generated site is navigable and rich
- generation process is reproducible and documented

**Labels**
`documentation` `tooling` `phpdocumentor` `p2`

---

### ISSUE ADR-001 — Record modular-monolith-first ADR and explicit out-of-scope list
**Summary**
Reduce architectural drift by documenting the intended platform shape and non-goals.

**Acceptance criteria**
- ADR states modular-monolith-first decision
- extraction criteria for future services are documented
- non-goals include GraphQL, gRPC, service mesh, vector DB, data lake, and similar distractions for current stage

**Labels**
`architecture` `adr` `governance` `p2`
