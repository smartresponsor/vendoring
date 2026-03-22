# Vendor Statement Delivery Runtime Canon

- Vendor remains the business root aggregate.
- User remains an external identity actor.
- Statement delivery runtime surfaces must keep ownership semantics adjacent to export and recipient seams.
- No external User aggregate is pulled into statement export or mailing flows.
- Export state is represented as vendor-local runtime data (`path`, `exists`, `readable`).
- Recipient discovery remains vendor-local and period-bound.
