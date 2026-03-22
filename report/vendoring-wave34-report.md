# Vendoring Wave 34 Report

Scope: live statement export flow.

Change made:
- Fixed payload-loss in `src/Controller/Vendor/Statement/VendorStatementExportController.php`.
- The export endpoint already built a statement in scoped request context (`tenantId`, `vendorId`, `from`, `to`, `currency`) and generated a PDF from that scoped data.
- But the HTTP response returned only `pdfBase64` and `path`, losing the request scope at the output boundary.

Result:
- Export response now preserves the effective statement scope alongside the generated artifact payload.
- No service contracts, DTO constructors, routes, or business calculations were changed.
