# Vendoring form type suffix cleanup audit

## Scope

Wave L closes a remaining type-folder naming drift in `src/Form/Ops`. Symfony form classes lived in the Form layer but still used the generic `*Type` suffix. In this ecosystem pass, Form-layer files should advertise their layer directly through the `*Form` suffix.

## Findings

- `src/Form/Ops/VendorTransactionCreateType.php` did not end with `Form`.
- `src/Form/Ops/VendorTransactionStatusUpdateType.php` did not end with `Form`.
- Both forms referenced DTO classes from `App\Vendoring\DTO\Ops` without explicit imports, making the forms fragile during stricter static/runtime checks.

## Changes

- Renamed `VendorTransactionCreateType` to `VendorTransactionCreateForm`.
- Renamed `VendorTransactionStatusUpdateType` to `VendorTransactionStatusUpdateForm`.
- Added explicit DTO imports to both form classes.
- Updated `VendorTransactionOperatorController` to use the renamed form classes.

## Deletions

The old `*Type.php` files are removed only as touched legacy files by the apply script. No repository-wide cleanup or destructive reset is required.
