# Vendoring Wave R — Doctrine mapping and operator label cleanup audit

## Scope

This pass keeps the component on the existing `App\Vendoring` namespace and only removes narrow structural drift found after the prior naming waves.

## Findings

- `config/packages/doctrine.yaml` pointed Doctrine attribute mapping at `src/Entity/VendorEntity`, while the actual entity tree is `src/Entity/Vendor`.
- The API documentation tag still exposed `VendorEntity Transactions` as user-facing wording.
- The server-rendered transaction operator page still exposed `VendorEntity` in page title, heading and form label, even though prior waves normalized user-facing labels to `Vendor`.

## Changes

- Doctrine mapping directory changed to `src/Entity/Vendor` while preserving the existing prefix `App\Vendoring\Entity\Vendor`.
- Nelmio tag label changed from `VendorEntity Transactions` to `Vendor Transactions`.
- Operator page wording changed from `VendorEntity ...` to `Vendor ...` for UI-facing labels only.

## Explicit non-goals

- No namespace migration.
- No entity class rename. `VendorEntity` remains the class name.
- No repository-wide deletion or root replacement.
