# Vendoring Wave P — Statement PDF acronym cleanup audit

## Scope

This wave finalizes the statement exporter class naming after the service/interface mirror work.

## Findings

- `src/Service/Statement` still contained `VendorStatementExporterPDFService`.
- `src/ServiceInterface/Statement` still contained `VendorStatementExporterPDFServiceInterface`.
- The all-caps `PDF` acronym made the class/file pair inconsistent with the component naming style used by other typed services.
- Consumers and DI aliases referenced the old symbols.
- Statement rendering still leaked `VendorEntity` wording into generated statement text/email subject.

## Changes

- Renamed `VendorStatementExporterPDFService` to `VendorStatementExporterPdfService`.
- Renamed `VendorStatementExporterPDFServiceInterface` to `VendorStatementExporterPdfServiceInterface`.
- Updated command, controller, runtime projection builder, and DI alias references.
- Reworded statement output from `VendorEntity Statement` to `Vendor Statement`.
- Reworded statement mail subject from `Monthly VendorEntity Statement` to `Monthly Vendor Statement`.

## Deletions

The old files are deleted by the apply script only as touched legacy files:

- `src/Service/Statement/VendorStatementExporterPDFService.php`
- `src/ServiceInterface/Statement/VendorStatementExporterPDFServiceInterface.php`

## Validation target

- `composer dump-autoload`
- `php bin/console lint:container`
- `php tests/bin/interface-alias-smoke.php`
