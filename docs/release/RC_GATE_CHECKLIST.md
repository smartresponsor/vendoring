# Vendoring RC Gate Checklist

## Status
- Component: Vendoring
- Release stage: Release Candidate
- Gate type: RC hardening / final integration
- Decision owner: <fill>
- Technical reviewer: <fill>
- Date: <fill>
- Branch / batch: workday-batch-a / workday-batch-b

---

## 1. Scope freeze

### Goal
Confirm that RC scope is sealed and that no uncontrolled feature expansion is entering the candidate.

### Checks
- [ ] RC scope is explicitly defined
- [ ] No new business domains were introduced during final hardening
- [ ] No architecture-wide churn was added after RC hardening started
- [ ] Only integration hardening, test reinforcement, documentation, and release gating are in scope
- [ ] Known follow-up work is separated into post-RC backlog

### Evidence
- Branch notes:
- Scope note:
- Reviewer comment:

---

## 2. Architecture baseline

### Goal
Confirm that the component remains structurally coherent and release-safe.

### Checks
- [ ] Runtime surfaces are explicit and stable
- [ ] Service boundaries remain Symfony-oriented and consistent
- [ ] No forbidden alternative root namespace exists
- [ ] No `/Domain/` structure was reintroduced
- [ ] Contracts remain separated from implementations
- [ ] Ownership-sensitive code paths are null-safe
- [ ] Statement delivery flow is integrated without architectural drift
- [ ] Security permission gating is explicit and deterministic

### Evidence
- Relevant packages / namespaces:
- Structural review summary:
- Reviewer comment:

---

## 3. Contract integrity

### Goal
Confirm that machine-facing and internal behavioral contracts are enforced.

### Checks
- [ ] Invalid permission resolves to null as expected
- [ ] Valid permission resolves vendor correctly
- [ ] Statement recipients are filtered by tenant and vendor
- [ ] Empty recipients are handled as non-error behavior
- [ ] Export occurs only on explicit request
- [ ] PDF metadata/path behavior is deterministic
- [ ] Exists flag behavior is stable
- [ ] Ownership-missing runtime paths degrade safely, not catastrophically

### Evidence
- Contract tests:
- Runtime notes:
- Reviewer comment:

---

## 4. Testing pyramid completion

### Goal
Confirm that RC is covered at the correct levels, not only by unit tests.

### Unit / component checks
- [ ] Unit tests pass
- [ ] Policy tests pass
- [ ] Payout tests pass
- [ ] Ledger tests pass
- [ ] Runtime consistency tests pass
- [ ] Profile readiness tests pass
- [ ] Statement delivery tests pass
- [ ] Security permission tests pass

### Integration checks
- [ ] Final integration batch passes locally
- [ ] Integration paths covering statement delivery are stable
- [ ] Integration paths covering permission gating are stable
- [ ] Integration paths covering null-safe ownership behavior are stable

### E2E checks
- [ ] Panther scenario: vendor profile create
- [ ] Panther scenario: vendor profile edit
- [ ] Panther scenario: publish flow
- [ ] Panther scenario: payout flow
- [ ] Panther scenario: statement send / delivery flow
- [ ] Panther scenario: runtime status / gated access surface

### Evidence
- Test command(s):
- Test report path:
- Reviewer comment:

---

## 5. Security gate

### Goal
Confirm that security behavior is explicit, reviewable, and stable.

### Checks
- [ ] Permission resolution is deterministic
- [ ] Invalid permission values do not escalate access
- [ ] Valid permission values resolve only expected vendor scope
- [ ] Unauthorized access paths fail safely
- [ ] No runtime explosion occurs under missing ownership / unresolved vendor conditions
- [ ] Security logic is covered by automated tests
- [ ] Security-sensitive behavior is documented at code level where needed

### Evidence
- Security test class(es):
- Review notes:
- Reviewer comment:

---

## 6. Statement delivery gate

### Goal
Confirm that statement delivery is production-safe at RC level.

### Checks
- [ ] Recipient filtering is tenant-safe
- [ ] Recipient filtering is vendor-safe
- [ ] Empty recipient list is treated gracefully
- [ ] Delivery/export split is intentional and documented
- [ ] PDF path behavior is stable
- [ ] PDF existence flag behavior is stable
- [ ] Statement delivery does not imply unintended export
- [ ] Runtime behavior stays deterministic when delivery targets are absent

### Evidence
- Statement delivery test class(es):
- Delivery notes:
- Reviewer comment:

---

## 7. Null-safe ownership runtime gate

### Goal
Confirm that missing ownership state cannot break runtime surfaces.

### Checks
- [ ] Ownership-null path is intentionally supported
- [ ] Ownership-null path is covered by tests
- [ ] Ownership-null path does not break runtime rendering
- [ ] Ownership-null path does not corrupt statement flow
- [ ] Ownership-null path does not bypass permission logic
- [ ] Failure mode is controlled and explainable

### Evidence
- Runtime scenarios:
- Null-safe behavior notes:
- Reviewer comment:

---

## 8. Documentation gate

### Goal
Confirm that RC is explainable to humans and machines.

### Checks
- [ ] Runtime canon is up to date
- [ ] Behavioral scenarios are documented
- [ ] Security-sensitive classes/methods have sufficient docblocks
- [ ] Statement delivery surfaces have sufficient docblocks
- [ ] Ownership-null runtime behavior is explained where needed
- [ ] phpDocumentor generation is configured
- [ ] phpDocumentor output builds successfully
- [ ] Generated docs location is recorded
- [ ] Nelmio / API-facing documentation is reviewed if applicable

### Evidence
- Documentation build command:
- Output path:
- Reviewer comment:

---

## 9. Operational readiness

### Goal
Confirm that the RC can be exercised and evaluated as a release artifact.

### Checks
- [ ] Release baseline is recorded
- [ ] RC build/install path is reproducible
- [ ] No hidden manual setup step blocks evaluation
- [ ] Runtime status page or equivalent operator surface is available
- [ ] Critical flows can be exercised in a deterministic environment
- [ ] Non-blocking follow-up items are separated from release blockers

### Evidence
- Baseline note:
- Runtime note:
- Reviewer comment:

---

## 10. Known non-blockers

### Goal
Prevent RC drift by separating polish from blockers.

### Rules
A non-blocker must:
- not violate contracts
- not break runtime safety
- not create security ambiguity
- not invalidate release confidence
- be safely deferrable to post-RC work

### Registered non-blockers
- [ ] <fill>
- [ ] <fill>
- [ ] <fill>

### Reviewer comment
-

---

## 11. Release blockers

### Goal
Track only issues that genuinely prevent RC issuance.

### Blocking criteria
A blocker exists if it:
- breaks contract determinism
- breaks permission safety
- breaks statement delivery correctness
- breaks null-safe runtime behavior
- breaks critical test confidence
- prevents reproducible release evaluation

### Registered blockers
- [ ] None
- [ ] <fill if needed>

### Reviewer comment
-

---

## 12. Final decision

### Technical verdict
- [ ] Not ready
- [ ] Conditionally ready
- [ ] RC ready
- [ ] RC approved for release packaging

### Summary
- Architecture baseline:
- Contract integrity:
- Test confidence:
- Security confidence:
- Documentation status:
- Non-blockers accepted:
- Blockers remaining:

### Sign-off
- Technical reviewer:
- Product / owner:
- Date:
