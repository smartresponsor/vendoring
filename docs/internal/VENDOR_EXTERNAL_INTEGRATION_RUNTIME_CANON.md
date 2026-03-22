# Vendor External Integration Runtime Canon

- Vendor remains the business root aggregate.
- External integration seams must stay vendor-local and read-side friendly.
- CRM, webhook consumer and payout bridge surfaces are reported without pulling an external User aggregate.
- Write-only neighboring services should expose runtime summaries through local projections instead of leaking remote provider contracts into controllers.
- Ownership/runtime context should remain adjacent to integration seams.
