# Vendoring Wave 12E — Cruding/View Alignment

## Purpose

Wave 12E keeps Vendoring zero-controller driven while making the HTTP route payload explicit for the Cruding and Viewing layers.

Vendoring remains a subject/component surface. It does not own the generic CRUD engine and it does not bind directly to Interfacing.

## Canon

- Route targets are `Vendor*Service` classes.
- Form targets are `Vendor*Type` classes.
- Controllers are not allowed.
- Doctrine persistence remains quarantined.
- Write/domain mutation routes remain blocked and inert.
- Read routes return a Viewing-compatible array payload.
- Vendoring does not import `App\Interfacing\*` directly.

## Response contract

`VendorHttpRouteResponseService` now adds two explicit contract sections to read and blocked responses:

- `cruding`
- `viewing`

The `cruding` section records the route grammar, operation, resolver and mutation permission.

The `viewing` section records the intended display surface, read model and template candidate.

This keeps read routes executable while persistence is unavailable and gives future Cruding/View integration a stable seam.

## Scope

Changed:

- `src/Service/Http/Vendor/VendorHttpRouteResponseService.php`
- `tools/qa/VendoringCrudingViewingAlignmentAudit.php`
- `docs/internal/VENDORING_CRUDING_VIEWING_ALIGNMENT.md`
- `delivery/audit/vendoring-wave12e-cruding-viewing-alignment.json`

Not changed:

- no controllers added
- no Doctrine entities added
- no repositories restored
- no Interfacing dependency added
- no write-side unblocked
- no files deleted

## Checks

```bash
php -l src/Service/Http/Vendor/VendorHttpRouteResponseService.php
php -l tools/qa/VendoringCrudingViewingAlignmentAudit.php
php tools/qa/VendoringCrudingViewingAlignmentAudit.php
php tools/qa/VendoringZeroControllerSurfaceAudit.php
php tools/qa/VendoringHttpReadRouteImplementationAudit.php
```
