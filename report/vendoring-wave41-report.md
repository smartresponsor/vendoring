# Vendoring wave41 report

## Scope
- Active base: cumulative snapshot wave40
- Focus: command-layer API key list flow boundary output

## Change
- Updated `src/Command/Vendor/VendorApiKeyListCommand.php`

## Why
The list command accepted `vendorId` and loaded a scoped key collection, but its console output lost that boundary context and did not expose aggregate list shape. This made manual ops and audit/debug less explicit than neighboring create/rotate flows.

## Result
- empty result now emits `vendorId` and `total=0`
- non-empty result now emits a scoped summary header with `vendorId` and `total`
- each row now preserves `vendorId`, `keyId`, `status`, `permissions`, `lastUsedAt`

## Verification
- `php -l src/Command/Vendor/VendorApiKeyListCommand.php` -> PASS
