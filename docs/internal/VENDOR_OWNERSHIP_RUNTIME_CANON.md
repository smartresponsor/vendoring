# Vendor Ownership Runtime Canon

- Vendor remains the business root aggregate.
- User remains an external identity actor and is represented only by scalar ids in Vendoring.
- VendorUserAssignment is the local ownership/access seam.
- VendorOwnershipView is the runtime read-side summary for owner and active assignments.
- API key access remains separate from human ownership/access semantics.
