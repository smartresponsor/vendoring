# Vendor Statement Delivery Runtime Canon

Defines the vendor statement delivery runtime surface.

Purpose:
- show statement build/export/recipient delivery readiness
- remain read-only
- support operator diagnostics and release review

Canonical inputs:
- tenantId
- vendorId
- optional period from/to
- currency
- optional export request semantics

Canonical output sections:
- ownership
- statement
- export
- recipients

Rules:
- read-only surface, no mutation
- ownership may be null for non-numeric vendor ids
- export may be null when export is not requested
- recipients must be filtered to the target tenant/vendor
- statement section represents the delivery-facing statement payload

Release meaning:
- no recipients does not imply crash, but does imply delivery incompleteness
- export readiness and recipient readiness are key operator signals
