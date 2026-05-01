# Vendoring form canon guardrail finalization audit

## Scope

This wave finalizes current documentation guardrails after the active Symfony form classes were renamed from `Vendor*Type` to `Vendor*Form`.

## Findings

- Active source now uses `src/Form/Ops/VendorTransactionCreateForm.php` and `src/Form/Ops/VendorTransactionStatusUpdateForm.php`.
- Root/current canon documentation still stated that `src/Form/**` must use `Vendor*Type.php` / `Vendor*Type`.
- That contradiction could cause future agents or contributors to reintroduce `*Type` classes into the cleaned `src/Form` layer.

## Changes

- Updated root README source-tree canon to require `Vendor*Form.php` / `Vendor*Form` in `src/Form/**`.
- Updated `docs/internal/LAYER3_STRUCTURE_NAMING_CANON.md` with the same rule.
- Preserved Symfony semantics: these classes remain Symfony form classes; only the repository filename/class suffix is canonicalized as `Form` for type-identifiable source layout.

## Non-goals

- No runtime behavior change.
- No namespace flattening.
- No repository-wide cleanup or destructive replacement.
- No changes to historical audit/manifest files from earlier waves, where `Vendor*Type` appears as rename history.
