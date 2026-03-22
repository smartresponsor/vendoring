# Vendoring Wave 112 — quality domain coverage expansion

## Summary
- added missing `@test:statement` to `quality`
- added missing `@test:payout` to `quality`
- added missing `@test:transaction-persistence` to `quality`
- expanded composer quality parity guards to require these entries

## Why
The scripts already existed in the active slice, but `quality` and its parity guards still treated them as optional, leaving a real orchestration gap between declared test slices and enforced quality coverage.
