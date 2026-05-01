# Vendoring API key interface wording cleanup audit

Wave U removes the last active source-level consumer-facing `VendorEntity` wording found outside the Entity/Repository implementation context.

## Findings

- `VendorApiKeyServiceInterface` still described the API key seam as a `VendorEntity API key service`.
- The interface legitimately accepts and returns `VendorEntity` because the current service contract is Doctrine/entity-backed. The issue was the descriptive phrase, not the PHP type.

## Changes

- Reworded the interface docblock from `VendorEntity API key service` to `Vendor API key service`.
- Left typed `VendorEntity` references untouched where they are actual PHP/Doctrine contract types.

## Non-goals

- No entity class rename.
- No repository/service contract redesign.
- No root-destructive cleanup.
