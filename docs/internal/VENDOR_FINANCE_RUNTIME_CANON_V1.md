# Vendor Finance Runtime Canon

Defines the vendor finance runtime surface.

Purpose:
- expose finance read-model state for a vendor
- remain read-only
- aggregate ownership, metric overview, payout account, and statement visibility

Canonical inputs:
- tenantId
- vendorId
- optional period from/to
- currency

Canonical output sections:
- ownership
- metricOverview
- payoutAccount
- statement

Rules:
- read-only surface, no mutation
- ownership may be null for non-numeric vendor ids
- payoutAccount may be null when no active account exists
- statement may be null when no complete period is supplied
- metricOverview is the main numeric summary block

Release meaning:
- missing payout account or statement readiness may degrade release confidence
- this surface feeds the runtime status aggregator
