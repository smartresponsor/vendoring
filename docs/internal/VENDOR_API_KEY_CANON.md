# Vendor API Key Canon

- Vendor is the business root aggregate.
- User remains an external identity actor and credential owner.
- VendorApiKeyService is the canonical machine-access seam for Vendoring.
- API key issuance, rotation, revocation and bearer-token vendor resolution belong here.
- Human credentials and login lifecycle remain outside Vendoring.
- Legacy `VendorSecurityService` / `VendorSecurityServiceInterface` compatibility aliases are retired; new callers must depend on `VendorApiKeyServiceInterface` or narrower security contracts.
