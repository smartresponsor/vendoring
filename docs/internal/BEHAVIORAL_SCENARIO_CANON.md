# Vendoring Behavioral Scenario Canon

## Purpose

This document defines how Vendoring business scenarios are described.

A behavioral scenario explains:
- who acts
- under what preconditions
- what operation occurs
- what state changes
- what read-model/runtime effect should follow
- what failure modes are expected

## Scenario template

Each scenario should contain:

- **ID**
- **Title**
- **Actor**
- **Preconditions**
- **Input / Trigger**
- **Expected mutation**
- **Expected projection/runtime effect**
- **Expected failure mode**
- **Release critical**: yes/no

## Actor types

Examples:
- new vendor
- existing vendor
- operator
- machine client via API key
- statement delivery process
- payout process

## Mutation vs projection rule

Scenarios should clearly distinguish:
- mutation expectation
- projection/runtime expectation

Example:
- mutation: payout record is created
- projection: runtime finance projection shows payout-related state

## Failure-mode rule

A scenario is incomplete unless it also states the main expected failure mode.

Examples:
- missing payout account
- incomplete public profile
- invalid permission
- no recipients
- invalid transaction status transition

## Cohort rule

Where useful, scenarios should identify cohort:
- new vendor
- established vendor
- incomplete config
- fully configured runtime
- machine-access client

## Release-critical rule

Mark a scenario as release-critical if a failure would materially damage:
- payout correctness
- statement delivery correctness
- runtime/operator trust
- profile/publication integrity
- machine-access control

## Output usage

Behavioral scenarios should be mapped later to one or more test layers:
- integration
- functional
- command
- runtime read-model
- browser E2E
- load/cohort harness

The same scenario may appear in multiple layers, but the canonical description should live here first.
