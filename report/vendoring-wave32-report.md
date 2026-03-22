# Vendoring wave32 report

## Scope
- `src/ServiceInterface/Vendor/Statement/StatementMailerServiceInterface.php`
- `src/Service/Vendor/Statement/StatementMailerService.php`

## Change
Repaired payload-loss in statement mailer flow. `send()` already accepted full delivery scope (`tenantId`, `vendorId`, `email`, `pdfPath`, `periodLabel`) but returned only `ok/message`. The response payload now preserves the accepted scope plus `attached` flag.

## Why
This keeps the live statement mailer flow honest for downstream audit/debug in the same way earlier waves preserved accepted inputs in other bridge/service payloads.
