# Vendoring wave38 report

- Base: wave37 cumulative snapshot.
- Scope: live contract honesty in vendor API key command flow.
- Change: expanded `src/RepositoryInterface/Vendor/VendorApiKeyRepositoryInterface.php` with `find()` and `findBy()` methods already used by live command-layer (`VendorApiKeyListCommand`, `VendorApiKeyRotateCommand`).
- Reason: commands were typed against the interface but invoked methods not declared by that interface, creating a real contract mismatch even if runtime repository implementation is outside the current slice.
- Safety: no business logic changed; this wave only aligned the repository contract with actual command usage.
