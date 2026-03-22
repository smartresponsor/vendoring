# Vendoring wave26 report

## Scope
- Replaced concrete statement-layer service injections with interface-based injections in controllers and command.

## Changes
- `src/Controller/Vendor/Payout/VendorStatementController.php`
- `src/Controller/Vendor/Statement/VendorStatementExportController.php`
- `src/Command/Vendor/SendVendorStatementsCommand.php`

## Rationale
- Statement flow already has canonical `ServiceInterface` contracts.
- Upper layers were still coupled to concrete implementations, which weakened replaceability and made wiring stricter than necessary.
- This wave narrows dependencies without changing business behavior or DTO shapes.

## Validation
- PHP lint on changed files
- vendoring structure scan
- vendoring PSR-4 scan
- vendoring missing-class scan v3
- vendoring quality gate v3
