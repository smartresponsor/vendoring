# Vendoring PHPDoc Canon

## Purpose

This document defines the PHPDoc and class/method documentation standard for Vendoring.

The target is not decorative comments. The target is explainable code.

## Class-level requirements

Every public class in the main runtime surface should state:

- its role
- its boundary
- whether it reads or mutates state
- what neighboring layers it coordinates with
- any important limitation or intentional non-goal

### Examples by class type

#### Mutation service
State what business action it performs and what side effects it may trigger.

#### Read-model builder
State what projections it assembles and that it is read-side only.

#### Policy
State what it validates/normalizes and what canonical failures it emits.

#### Command
State what operational flow it triggers and whether it is orchestration-only.

#### Repository interface
State what aggregate/entity family it exposes and any important lookup contract.

## Method-level requirements

Every meaningful public method should document:

- intent
- expected input semantics
- output semantics
- exceptions or canonical failure cases
- side effects if any

## Private method rule

Private methods do not need PHPDoc unless:
- the logic is non-obvious
- there is a normalization rule worth preserving
- there is a tricky invariant or fallback

## DTO/value object rule

Document DTOs/value objects when:
- fields have business semantics not obvious from names
- the object forms part of a contract
- normalization/invariants matter

Do not add noise to purely obvious shape containers unless they are externally important.

## Side-effect markers

Where relevant, explicitly mention:

- Persists state
- Dispatches events
- Emits metrics
- Reads filesystem
- Performs external transport
- Side effects: none

## Exception rule

If a public method can throw meaningful domain/application exceptions,
document the canonical cases.

Prefer documenting the behavior in semantic terms, not just listing exception class names.

## phpDocumentor compatibility

Docblocks should remain compatible with phpDocumentor.

Use:
- clear summaries
- useful long descriptions where needed
- `@return`, `@param`, `@throws` when they add meaning
- precise array-shape descriptions where this helps understanding

## Anti-patterns

Forbidden:
- empty comments
- boilerplate comments that restate code
- stale comments after code changes
- pretending a fallback service is “fully integrated” when it is intentionally local/no-op

## Release-candidate expectation

Vendoring PHPDoc is RC-ready when:
- public mutation and runtime seams are documented
- docblocks match actual behavior
- generated docs are useful for humans and machines
