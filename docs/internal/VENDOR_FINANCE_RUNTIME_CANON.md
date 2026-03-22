# Vendor Finance Runtime Canon

- Vendor remains the business root aggregate.
- User remains an external identity actor.
- Ownership/access semantics are surfaced through vendor-local read models.
- Finance-facing runtime summaries may include ownership context, but must not pull an external User aggregate.
- Payout account, metric overview and statement payloads remain vendor-local finance surfaces.
- Credentials and human-auth concerns remain outside Vendoring.
