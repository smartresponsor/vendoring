# Vendoring Wave 127: Vendor service naming audit

## Scope
Validated and applied the naming rule for component-scoped services:

- Services directly bound to the Vendor component use `Vendor*Service`.
- Independent reusable services may keep neutral names.

## Applied renames

| Old class | New class | Old file | New file |
|---|---|---|---|
| `App\Service\CrmService` | `App\Service\VendorCrmService` | `src/Service/CrmService.php` | `src/Service/VendorCrmService.php` |
| `App\Service\Ledger\DoubleEntryService` | `App\Service\Ledger\VendorDoubleEntryService` | `src/Service/Ledger/DoubleEntryService.php` | `src/Service/Ledger/VendorDoubleEntryService.php` |
| `App\Service\Ledger\LedgerService` | `App\Service\Ledger\VendorLedgerService` | `src/Service/Ledger/LedgerService.php` | `src/Service/Ledger/VendorLedgerService.php` |
| `App\Service\Payout\PayoutAccountService` | `App\Service\Payout\VendorPayoutAccountService` | `src/Service/Payout/PayoutAccountService.php` | `src/Service/Payout/VendorPayoutAccountService.php` |
| `App\Service\Payout\PayoutRequestService` | `App\Service\Payout\VendorPayoutRequestService` | `src/Service/Payout/PayoutRequestService.php` | `src/Service/Payout/VendorPayoutRequestService.php` |
| `App\Service\Payout\PayoutService` | `App\Service\Payout\VendorPayoutService` | `src/Service/Payout/PayoutService.php` | `src/Service/Payout/VendorPayoutService.php` |
| `App\Service\Statement\StatementMailerService` | `App\Service\Statement\VendorStatementMailerService` | `src/Service/Statement/StatementMailerService.php` | `src/Service/Statement/VendorStatementMailerService.php` |
| `App\Service\WebhooksConsumer\WebhooksConsumerService` | `App\Service\WebhooksConsumer\VendorWebhooksConsumerService` | `src/Service/WebhooksConsumer/WebhooksConsumerService.php` | `src/Service/WebhooksConsumer/VendorWebhooksConsumerService.php` |

## Verification

```bash
php tools/vendoring-service-naming-audit.php
```

Result: `violations: []`.
