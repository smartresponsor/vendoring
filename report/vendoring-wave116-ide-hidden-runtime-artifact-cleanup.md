# Vendoring Wave 116 - IDE Hidden Runtime Artifact Cleanup

## Summary
- removed `.ide/sr_default_inspector.xml` from cumulative snapshot
- expanded IDE runtime artifact guard to cover `.ide/` and `.idea/` runtime leftovers consistently

## Why
The active cumulative snapshot still contained `.ide/sr_default_inspector.xml`, which is machine-local IDE state and not part of the canonical source-of-truth repository slice.
