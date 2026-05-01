# Vendoring VendorEntity wording cleanup audit

## Scope

Wave T narrows the remaining `VendorEntity` wording in consumer-facing or projection-facing surfaces. The class name `VendorEntity` remains valid inside Doctrine/entity code and typed PHP contracts.

## Findings

- README product positioning and API sections still used `VendorEntity` as a business-facing label.
- Local development HTML linked to the transaction API as `VendorEntity transactions API`.
- Statement export controller PHPDoc described request input as a `VendorEntity identifier`.
- Projection docblocks used `VendorEntity-local` wording even though projections are part of the read/API surface.

## Changes

- Reworded consumer/business documentation to `Vendor`.
- Kept `VendorEntity` only where it names the actual Doctrine/PHP entity class.
- Clarified README vocabulary: `Vendor` is the business/API term; `VendorEntity` is an implementation class term.

## Non-goals

- No entity class rename.
- No Doctrine mapping change.
- No namespace change.
- No repository-wide deletion or destructive reset.
