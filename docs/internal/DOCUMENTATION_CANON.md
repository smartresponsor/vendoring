# Vendoring Documentation Canon

## Purpose

This document defines the documentation contract for the Vendoring component.

The goal is not to maximize comment volume. The goal is to make the component
readable, reviewable, explainable, and machine-consumable at release-candidate
quality.

Documentation must serve both:
- a human engineer reviewing behavior and architecture
- a machine agent generating, validating, and evolving the component

## Documentation layers

Vendoring uses four distinct documentation layers:

1. **API documentation**
   - HTTP endpoints
   - request/response contracts
   - error contracts
   - auth/security expectations

2. **Static code documentation**
   - classes
   - public methods
   - service boundaries
   - invariants
   - side effects

3. **Behavioral scenario documentation**
   - business flows
   - expected state changes
   - projection/runtime expectations
   - failure modes

4. **Operational/release documentation**
   - runtime surfaces
   - release baseline expectations
   - RC readiness and gaps

These layers are complementary and must not be collapsed into one generic style.

## Required documentation targets

The following targets must be documented:

- all public controllers
- all public commands
- all public services
- all public builders/readers/resolvers
- all policies
- all repository interfaces
- all public DTO/value objects where semantics are not obvious

The following targets may be documented selectively:

- trivial entities with self-evident getters/setters
- private methods with no behavioral ambiguity
- purely mechanical test helpers

## Documentation quality rule

Documentation must explain:
- what the class or method is for
- what system boundary it belongs to
- whether it mutates state or only reads/builds projections
- what invariants it expects
- what outputs or side effects it produces
- what failures are expected and meaningful

Documentation must not merely restate the code.

## Mutation vs projection rule

Every public class should be understandable as one of these:

- **mutation service**
- **projection/read-model builder**
- **policy/validator**
- **adapter/integration bridge**
- **controller/command entrypoint**
- **repository contract**
- **DTO/value object**

The class-level docblock should make this explicit.

## Side effects rule

Where relevant, documentation must explicitly state whether a method:
- persists state
- dispatches events
- emits metrics
- performs file I/O
- performs external transport/integration work
- is intentionally side-effect free

## Release-candidate expectation

For Vendoring to be considered RC-ready from a documentation perspective:

- all public runtime and mutation seams must have meaningful docblocks
- API endpoints must have API-level contract documentation
- critical business scenarios must be documented
- documentation must be stable enough to serve as a review baseline

## Anti-patterns

The following are forbidden:

- empty docblocks
- comments that only repeat parameter names
- “getter returns X” style noise
- misleading future-tense comments not grounded in current code
- comments that describe desired behavior when code does something else

## Preferred style

Documentation should be:
- direct
- precise
- layer-aware
- behavior-oriented
- compatible with phpDocumentor and API tooling
