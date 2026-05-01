# Vendoring Wave P — Statement PDF acronym cleanup patch manifest

## Added / replaced

- `src/Service/Statement/VendorStatementExporterPdfService.php`
- `src/ServiceInterface/Statement/VendorStatementExporterPdfServiceInterface.php`

## Updated

- `src/Command/VendorSendVendorStatementsCommand.php`
- `src/Controller/Vendor/VendorStatementExportController.php`
- `src/Service/Statement/VendorStatementDeliveryRuntimeProjectionBuilderService.php`
- `src/Service/Statement/VendorStatementMailerService.php`
- `config/component/services.yaml`

## Deleted by apply script

- `src/Service/Statement/VendorStatementExporterPDFService.php`
- `src/ServiceInterface/Statement/VendorStatementExporterPDFServiceInterface.php`

## Notes

The patch is intentionally narrow: it does not change statement export or mailer control flow.
