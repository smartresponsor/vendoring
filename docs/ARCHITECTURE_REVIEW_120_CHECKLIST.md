# Vendoring Architecture Review — 120-Point Checklist

## Scope and evaluation basis

This review is based on the current repository slice as a Symfony-oriented modular monolith with:
- strong contract and test emphasis,
- significant runtime hardening work already present,
- release-candidate governance artifacts,
- partial but growing operational maturity.

Assessment scale:
- missing
- weak
- partial
- solid
- strong

## Executive verdict

Vendoring is currently best understood as a **contract-heavy modular monolith** with strong core domain direction, solid API/runtime boundaries, and a growing operational readiness layer.

It is **not yet a production-grade distributed operating platform**.

### Strongest areas
- modular-monolith structure
- DTO / contract discipline
- contract and runtime testing
- semantic smoke checks and synthetic probes
- logging / correlation / metrics baseline
- release-readiness documentation and explainability work

### Main gaps
- enforced authentication and authorization on all public write paths
- real observability backend and monitoring/alerting
- fault-tolerance runtime mechanisms (breaker/bulkhead/retry enforcement)
- explicit capacity/performance evidence
- deploy automation / rollback / canary automation
- browser-level E2E coverage

## 120-item review

### 1. Scalability
- Relevance: High.
- Maturity: partial.
- Implemented: modular monolith, tenant/vendor scoping, rollout seam, synthetic probes.
- Partial: capacity assumptions are still implicit.
- Missing: performance budgets, async offload roadmap, cluster-safe limiter.
- Risks: hottest DB paths and file-based infrastructure seams will not scale horizontally.
- Improvement: define hot paths, capacity assumptions, async extraction candidates, and multi-node-safe infrastructure seams.
- Trade-off: current simplicity helps correctness but delays scale predictability.

### 2. Availability
- Relevance: High.
- Maturity: weak.
- Implemented: strong local quality gates and post-deploy verification pack.
- Partial: operational readiness documentation exists.
- Missing: HA topology, failover assumptions, operational runbooks.
- Risks: system behavior under infra/node loss is unspecified.
- Improvement: define single-node assumptions explicitly and later add HA/failover story.

### 3. Reliability
- Relevance: High.
- Maturity: partial.
- Implemented: contract tests, semantic smokes, idempotency semantics for strongest slice.
- Partial: outbound operation policy seam exists.
- Missing: unified error taxonomy and runtime fault-handling matrix.
- Risks: downstream instability may still surface inconsistently.
- Improvement: formal fault-tolerance policy, breaker/bulkhead, retry enforcement.

### 4. Latency
- Relevance: High.
- Maturity: weak.
- Implemented: runtime metrics baseline.
- Partial: synthetic probes show corridor integrity, not latency targets.
- Missing: SLOs, measured budgets, hot-path profiling.
- Risks: regressions stay invisible until late.
- Improvement: define latency budgets for create/list/update and payout/statement corridors.

### 5. Throughput
- Relevance: High.
- Maturity: weak.
- Implemented: no direct throughput evidence yet.
- Missing: load tests, write/read ceilings, concurrency evidence.
- Risks: unknown throughput ceiling.
- Improvement: create focused load harness for hottest public corridors.

### 6. Capacity
- Relevance: High.
- Maturity: missing.
- Implemented: none explicitly.
- Missing: expected RPS, daily volume, write intensity assumptions.
- Risks: no sizing discipline for DB/runtime.
- Improvement: record capacity model and database/runtime assumptions.

### 7. Client-Server
- Relevance: High.
- Maturity: solid.
- Implemented: HTTP APIs, CLI/operator entrypoints, transport DTO/forms.
- Partial: access model still needs stronger enforcement.
- Missing: none essential for current architecture.
- Trade-off: keeping one app boundary reduces complexity.

### 8. Database
- Relevance: High.
- Maturity: solid.
- Implemented: Doctrine ORM/DBAL, migrations, SQL-centric transactional model.
- Partial: zero-downtime readiness is still policy-heavy rather than automated.
- Missing: operational DB topology guidance.
- Risks: production topology remains underdefined.
- Improvement: formalize DB runtime assumptions and migration/deploy choreography.

### 9. SQL vs NoSQL
- Relevance: High.
- Maturity: strong.
- Implemented: SQL is the correct fit for transaction/payout/statement consistency.
- Intentionally not needed: NoSQL as primary store.
- Trade-off: stricter schema discipline in exchange for ACID correctness.

### 10. Load Balancing
- Relevance: Medium.
- Maturity: missing.
- Implemented: none.
- Missing: stateless multi-node assumptions, shared limiter state.
- Risks: current file-backed seams break under horizontal scale.
- Improvement: move sensitive local state to shared backing services before load balancing.

### 11. Caching
- Relevance: Medium.
- Maturity: missing.
- Implemented: no major cache layer.
- Intentionally not needed: aggressive caching at this stage.
- Risks: premature caching would likely create correctness drift.
- Improvement: only introduce cache after hot-path evidence exists.

### 12. Cache Invalidation
- Relevance: Medium.
- Maturity: missing.
- Current stance: not yet needed because caching is not central.
- Risk: future cache addition without invalidation design will be dangerous.
- Improvement: couple any future cache work with explicit invalidation policy.

### 13. CDN
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed: current API/operator focus does not justify CDN complexity.

### 14. DNS
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed at repo level now.

### 15. API Design
- Relevance: High.
- Maturity: solid.
- Implemented: stable JSON contracts, error codes, operational headers, documentation.
- Partial: consistency of all headers/versioning/auth contracts still needs finishing.
- Improvement: continue normalizing all public responses.

### 16. REST
- Relevance: High.
- Maturity: solid.
- Implemented: resource-oriented endpoints and JSON responses.
- Partial: some operator/runtime endpoints are operational rather than purely RESTful.
- Trade-off: operational clarity is preferred over ideological purity.

### 17. GraphQL
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 18. gRPC
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 19. Authentication
- Relevance: High.
- Maturity: partial.
- Implemented: API key domain seams and commands.
- Partial: public-write-path enforcement is not yet uniformly strong.
- Missing: clear 401/403 enforcement and full behavioral coverage across all write endpoints.
- Improvement: make auth an active runtime contract, not only a documented seam.

### 20. Authorization
- Relevance: High.
- Maturity: partial.
- Implemented: ownership and assignment model.
- Partial: role/capability model needs centralized runtime enforcement.
- Missing: one canonical authorization matrix used everywhere.
- Improvement: enforce matrix in controllers/services and cover with decision tests.

### 21. Rate Limiting
- Relevance: High.
- Maturity: partial.
- Implemented: public write rate-limit contract and stable headers.
- Partial: current implementation is local/file-backed.
- Missing: cluster-safe/shared limiter backend.
- Risks: wrong behavior under multi-node rollout.
- Improvement: replace local limiter with shared backend.

### 22. Fault Tolerance
- Relevance: High.
- Maturity: weak.
- Implemented: partial policy seam.
- Missing: end-to-end policy and runtime guards.
- Risks: unreliable downstream behavior and inconsistent degradation.
- Improvement: define error classes and runtime handling matrix.

### 23. High Availability
- Relevance: Medium.
- Maturity: missing.
- Missing: HA topology, redundancy assumptions, failover discipline.

### 24. CAP Theorem
- Relevance: Medium.
- Maturity: weak.
- Current stance: implicitly single-primary/strong-consistency leaning.
- Missing: explicit statement of distributed assumptions.
- Improvement: document that distributed CAP trade-offs are largely deferred by modular-monolith-first strategy.

### 25. Consistency Models
- Relevance: High.
- Maturity: partial.
- Implemented: ACID-centric transactional consistency.
- Partial: read-side projections exist but consistency is not classified per flow.
- Improvement: document consistency expectations per corridor.

### 26. Replication
- Relevance: Medium.
- Maturity: missing.
- Missing: replication strategy and read/write topology.

### 27. Partitioning
- Relevance: Medium.
- Maturity: missing.
- Intentionally not needed yet.
- Note: tenant/vendor scoping lays groundwork for future partitioning.

### 28. Sharding
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 29. Indexing
- Relevance: High.
- Maturity: partial.
- Implemented: transaction uniqueness/index-oriented migration discipline.
- Missing: measured hot-query and composite-index review.
- Improvement: perform explicit indexing review against actual query patterns.

### 30. Denormalization
- Relevance: Medium.
- Maturity: weak.
- Implemented: projections/read models rather than DB denormalization.
- Missing: formal denormalization strategy.

### 31. ACID
- Relevance: High.
- Maturity: strong.
- Implemented: correct transactional orientation for core domain.

### 32. BASE
- Relevance: Low/Medium.
- Maturity: missing.
- Intentionally not central.

### 33. Microservices
- Relevance: Medium.
- Maturity: intentionally absent.
- Current decision: modular monolith first.
- Improvement: record ADR explicitly.

### 34. Monolith
- Relevance: High.
- Maturity: solid.
- Implemented: strong Symfony-oriented modular monolith structure.
- Risks: hidden coupling through shared container/entities.
- Improvement: formal dependency rules.

### 35. Event-Driven Architecture
- Relevance: Medium.
- Maturity: partial.
- Implemented: events and payload-event surfaces.
- Missing: asynchronous event backbone.
- Trade-off: local events keep things simpler now.

### 36. Message Queue
- Relevance: Medium/High.
- Maturity: missing.
- Missing: queue-based offload for mail/heavy integrations/audit.
- Improvement: async roadmap.

### 37. Pub/Sub
- Relevance: Medium.
- Maturity: missing.
- Intentionally not needed yet.

### 38. Sync vs Async
- Relevance: High.
- Maturity: partial.
- Implemented: sync-first architecture.
- Missing: explicit async extraction criteria.
- Trade-off: predictability today, lower scalability tomorrow.

### 39. Idempotency
- Relevance: High.
- Maturity: partial.
- Implemented: canonical uniqueness contract and duplicate semantics in strongest slice.
- Partial: idempotency public contract should be normalized across relevant endpoints.
- Improvement: align docs, headers, tests, and OpenAPI.

### 40. Backpressure
- Relevance: Medium.
- Maturity: missing.
- Implemented: rate limiting partially helps.
- Missing: systemic load management.

### 41. Circuit Breaker
- Relevance: Medium/High.
- Maturity: missing.
- Improvement: add provider isolation around outbound calls.

### 42. Bulkhead
- Relevance: Medium.
- Maturity: missing.
- Improvement: isolate mail/provider failures from the whole request path.

### 43. Retry Logic
- Relevance: High.
- Maturity: weak/partial.
- Implemented: policy seam direction.
- Missing: actual retry execution rules.

### 44. Timeout
- Relevance: High.
- Maturity: weak/partial.
- Implemented: policy language only.
- Missing: enforced timeout behavior in transport adapters.

### 45. Service Discovery
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 46. API Gateway
- Relevance: Low/Medium.
- Maturity: missing.
- Intentionally not needed.

### 47. Load Shedding
- Relevance: Medium.
- Maturity: missing.
- Missing: targeted overload response beyond rate limiting.

### 48. Autoscaling
- Relevance: Medium.
- Maturity: missing.
- Missing: runtime deployment model and scale assumptions.

### 49. Blue-Green Deployment
- Relevance: Medium.
- Maturity: weak.
- Partial: release/deploy docs direction exists.
- Missing: actual deployment automation.

### 50. Canary Release
- Relevance: High.
- Maturity: partial.
- Implemented: feature flags, cohort resolver, probes.
- Missing: deployment-side automation and managed rollout workflows.

### 51. Feature Flags
- Relevance: High.
- Maturity: partial.
- Implemented: canonical feature-flag service and cohort resolution seam.
- Missing: persistent storage, admin tooling, percentage rollout.
- Improvement: move from static config seam toward operable rollout layer.

### 52. Observability
- Relevance: High.
- Maturity: partial.
- Implemented: logs, correlation, metrics baseline, synthetic probes.
- Missing: real backend, dashboards, alert integration.

### 53. Logging
- Relevance: High.
- Maturity: solid.
- Implemented: structured runtime logging envelope.
- Partial: adoption breadth can still grow.

### 54. Metrics
- Relevance: High.
- Maturity: partial.
- Implemented: runtime metric collector.
- Missing: external export backend and operational dashboards.

### 55. Tracing
- Relevance: Medium.
- Maturity: weak.
- Implemented: correlation IDs.
- Missing: real tracing spans and trace backend.

### 56. Correlation ID
- Relevance: High.
- Maturity: solid.
- Implemented: subscriber, context storage, response echo.

### 57. Monitoring
- Relevance: High.
- Maturity: missing.
- Missing: dashboards, SLO views, operational review loop.

### 58. Alerting
- Relevance: High.
- Maturity: missing.
- Missing: alert conditions and response guidance.

### 59. Full-Text Search
- Relevance: Low/Medium.
- Maturity: weak/partial.
- Not central to current architecture.

### 60. Time Series
- Relevance: Low.
- Maturity: missing.
- Not needed until metrics backend matures.

### 61. Vector DB
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 62. Materialized View
- Relevance: Medium.
- Maturity: weak.
- Projections exist, DB materialized views do not.

### 63. Query Optimization
- Relevance: High.
- Maturity: partial.
- Implemented: some index-aware discipline.
- Missing: measured query optimization work.

### 64. Connection Pooling
- Relevance: Medium.
- Maturity: weak.
- Missing: explicit pool sizing/operational assumptions.

### 65. Cache Stampede
- Relevance: Low/Medium.
- Maturity: missing.
- Currently acceptable because cache is not central.

### 66. Cache Warming
- Relevance: Low.
- Maturity: missing.
- Not needed yet.

### 67. CDN Caching
- Relevance: Low.
- Maturity: missing.
- Not needed now.

### 68. Data Compression
- Relevance: Low/Medium.
- Maturity: missing.
- Not currently a priority.

### 69. Serialization
- Relevance: High.
- Maturity: partial.
- Implemented: DTOs/forms/contracts and JSON payloads.
- Partial: arrays still exist in some support seams.
- Improvement: continue transport contract normalization.

### 70. Deserialization
- Relevance: High.
- Maturity: partial.
- Implemented: form/input transport layer.
- Missing: one universal normalization policy everywhere.

### 71. WebSockets
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 72. WebRTC
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 73. CQRS
- Relevance: Medium.
- Maturity: partial.
- Implemented: read-side builders/projections.
- Missing: strict CQRS partitioning across whole app.
- Trade-off: current softer model is enough.

### 74. Event Sourcing
- Relevance: Low/Medium.
- Maturity: missing.
- Intentionally not needed now.

### 75. Service Mesh
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 76. Sidecar
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 77. BFF
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 78. Strangler Pattern
- Relevance: Low/Medium.
- Maturity: missing.
- Not yet needed.

### 79. LSM Trees
- Relevance: Low.
- Maturity: not applicable.

### 80. B-Trees
- Relevance: Medium.
- Maturity: partial.
- Implicit through SQL indexing; policy still underdeveloped.

### 81. Merkle Trees
- Relevance: Low.
- Maturity: missing.
- Not needed.

### 82. Bloom Filter
- Relevance: Low.
- Maturity: missing.
- Not needed.

### 83. HyperLogLog
- Relevance: Low.
- Maturity: missing.
- Not needed.

### 84. MapReduce
- Relevance: Low.
- Maturity: missing.
- Not needed.

### 85. Batch Processing
- Relevance: Medium.
- Maturity: partial.
- Implemented: CLI command surfaces.
- Missing: explicit batch processing policy/runbook.

### 86. Stream Processing
- Relevance: Low.
- Maturity: missing.
- Not needed.

### 87. ETL
- Relevance: Low/Medium.
- Maturity: missing.
- Not a current core concern.

### 88. Data Pipeline
- Relevance: Low/Medium.
- Maturity: missing.
- Not a current core concern.

### 89. Data Lake
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 90. Data Warehouse
- Relevance: Low.
- Maturity: missing.
- Intentionally not needed.

### 91. Secrets Management
- Relevance: High.
- Maturity: weak/partial.
- Implemented: domain-level API-key surfaces.
- Missing: deployment-grade secret manager and rotation path.

### 92. RBAC
- Relevance: High.
- Maturity: partial.
- Implemented: ownership and assignment semantics.
- Missing: fully enforced canonical role/capability matrix across app.

### 93. SSO
- Relevance: Low/Medium.
- Maturity: missing.
- Intentionally not needed now.

### 94. Encryption
- Relevance: High.
- Maturity: weak/partial.
- Missing: explicit encryption posture documentation and sensitive-field treatment.

### 95. Checksum
- Relevance: Medium.
- Maturity: weak.
- Missing: generalized artifact/payload checksum discipline.

### 96. Erasure Coding
- Relevance: Low.
- Maturity: missing.
- Not relevant.

### 97. Consensus
- Relevance: Low.
- Maturity: missing.
- Not relevant.

### 98. Leader Election
- Relevance: Low.
- Maturity: missing.
- Not relevant.

### 99. Domain boundaries
- Relevance: High.
- Maturity: partial.
- Implemented: natural context separation in repo structure.
- Missing: enforced dependency rules.

### 100. Bounded Contexts
- Relevance: High.
- Maturity: partial.
- Implemented: emergent contexts.
- Missing: explicit context map and ownership rules.

### 101. DTO design
- Relevance: High.
- Maturity: solid/partial.
- Implemented: DTO/forms layer with improved PHPDoc.
- Partial: some seams still use arrays.

### 102. Contract design
- Relevance: High.
- Maturity: solid.
- Implemented: interfaces, projections, stable error codes, semantic smokes.
- Partial: some headers/contracts still uneven across endpoints.

### 103. API versioning
- Relevance: High.
- Maturity: weak/partial.
- Implemented: docs direction exists.
- Missing: fully active and universal runtime contract.

### 104. Schema evolution
- Relevance: High.
- Maturity: partial.
- Implemented: migration discipline and parity testing.
- Missing: enforced policy in delivery workflow.

### 105. Zero-downtime migration readiness
- Relevance: High.
- Maturity: weak/partial.
- Implemented: playbook direction.
- Missing: automated rollout discipline.

### 106. Testing strategy
- Relevance: High.
- Maturity: strong.
- Implemented: unit, integration, semantic smoke, runtime synthetic probes.
- Partial: browser E2E not yet in place.

### 107. Contract testing
- Relevance: High.
- Maturity: strong.
- Implemented: semantic contract smokes and tests around strongest slices.

### 108. E2E testing
- Relevance: High.
- Maturity: weak.
- Missing: browser-level end-to-end coverage.

### 109. Behavioral testing
- Relevance: High.
- Maturity: partial.
- Implemented: synthetic corridor probes.
- Missing: richer user/cohort journeys.

### 110. Security posture
- Relevance: High.
- Maturity: partial.
- Implemented: API key seams, rate limiting, ownership, logging, correlation.
- Missing: full auth enforcement, threat model, secret management maturity.

### 111. Threat modeling
- Relevance: High.
- Maturity: missing.
- Missing: explicit adversary model and abuse/use-case review.

### 112. CI/CD readiness
- Relevance: High.
- Maturity: solid/partial.
- Implemented: rich scripts, RC artifacts, probes, semantic smokes.
- Missing: deploy automation and alert-linked rollout.

### 113. Rollback strategy
- Relevance: High.
- Maturity: weak.
- Missing: rollback manifest and operational steps.

### 114. Kubernetes readiness
- Relevance: Medium.
- Maturity: weak.
- Current repo could evolve there, but infra/readiness layer is not mature enough yet.

### 115. Traffic cohort strategy
- Relevance: High.
- Maturity: partial.
- Implemented: cohort resolver and feature-flag seam.
- Missing: percentage rollout, persistence, runtime controls.

### 116. Synthetic / canary users
- Relevance: High.
- Maturity: partial/solid.
- Implemented: transaction, finance, payout, and post-deploy probes.
- Missing: remote automated canary execution tied to rollout system.

### 117. Documentation quality
- Relevance: High.
- Maturity: solid.
- Implemented: release docs, phase docs, PHPDoc wave, public-surface documentation.
- Partial: rich phpDocumentor site remains temporary-generator-based.

### 118. Operational clarity
- Relevance: High.
- Maturity: partial/solid.
- Implemented: runtime surfaces, probes, RC documentation, logging/correlation docs.
- Missing: incident runbooks, dashboards, alerting.

### 119. Architectural consistency
- Relevance: High.
- Maturity: solid/partial.
- Implemented: Symfony-oriented structure, contracts, layered public surfaces.
- Risks: mixed maturity between contexts and hidden coupling.
- Improvement: formal context/dependency rules and continued cleanup.

### 120. Technical debt level
- Relevance: High.
- Maturity: partial/manageable.
- Current debt is mostly operational/distributed, not core-domain chaos.
- Main risk: adding more breadth before hardening auth/ops/reliability.

## Prioritized action list

### P0 — Must before real production exposure
1. Enforce canonical authentication on public write endpoints.
2. Formalize and enforce RBAC authorization matrix.
3. Replace local metrics collector path with real observability backend.
4. Define unified fault-tolerance policy.
5. Add circuit breaker / bulkhead around outbound providers.
6. Make API versioning a universal runtime contract.
7. Enforce schema evolution policy and zero-downtime migration discipline.

### P1 — Strong next wave
8. Replace file-backed rate limiter with deployment-safe backend.
9. Normalize idempotency public contract everywhere it applies.
10. Build capacity and hot-path performance baseline.
11. Create async offloading roadmap.
12. Add browser-level E2E coverage.
13. Introduce monitoring dashboards and alerting.

### P2 — After RC stabilization
14. Wire feature flags + cohorts + probes into real canary rollout.
15. Create rollback manifest and rollback procedure.
16. Introduce production secrets management.
17. Define encryption posture.
18. Replace temporary phpDocumentor path with rich upstream generator.
19. Record modular-monolith-first ADR and out-of-scope list.
