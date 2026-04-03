# RC Rollback Manifest

This document describes the rollback decision surface used after a release manifest has been assembled.

## Purpose

The rollback manifest converts the current release state into one operational decision:
- `proceed`
- `hold`
- `rollback`

## Decision rules

### Rollback

Rollback is recommended when:
- a critical alert is present
- one or more outbound circuit breakers are open

### Hold

Hold is recommended when:
- release docs are missing
- build artifacts are missing
- synthetic probe artifacts are missing
- non-critical warning alerts are present

### Proceed

Proceed is recommended only when the release manifest is green and no hold/rollback condition is active.

## Actions

The rollback decision includes an `actions` list to guide operators without requiring external runbooks.
