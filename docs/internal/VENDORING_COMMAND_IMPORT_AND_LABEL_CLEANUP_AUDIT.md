# Vendoring command import and user-facing label cleanup audit

## Scope

Wave Q removes low-risk command-file drift left by previous interface-typing waves.

## Findings

- Several command classes contained duplicate command-service imports after earlier interface-typing passes.
- Several CLI option/help/error strings exposed implementation class terminology (`VendorEntity`) instead of the business-facing `Vendor` wording.
- Demo fixture display names and statement email body text leaked `VendorEntity` wording into generated/demo or user-visible surfaces.

## Changes

- Deduplicated import lines in affected command classes without changing constructor signatures or command behavior.
- Changed command option descriptions from `VendorEntity ID` to `Vendor ID`.
- Changed the API-key not-found message from `VendorEntity not found` to `Vendor not found`.
- Changed demo fixture labels from `VendorEntity Demo/Group` to `Vendor Demo/Group`.
- Changed statement email text from `VendorEntity` to `Vendor`.

## Non-goals

- No namespace migration.
- No entity class rename.
- No broad service refactor.
- No destructive repository replacement.
