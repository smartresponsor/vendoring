# Vendor Security State Canon

- `Vendor` remains the business root aggregate.
- `VendorSecurity` is transitional vendor-local state, not the human identity/authentication model.
- Canonical machine access lives in `VendorApiKeyService` and `VendorApiKey`.
- Human credentials and Symfony security users remain outside Vendoring.
- Prefer reading transitional state through `VendorSecurityStateProjection` when a lightweight runtime shape is needed.
