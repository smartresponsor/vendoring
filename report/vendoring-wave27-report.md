# Vendoring wave27 report

Current slice base: wave26 cumulative snapshot.

## What changed
- Switched top-level Vendor controllers from concrete service/repository injections to interface injections where canonical contracts already existed.
- Touched controllers:
  - `src/Controller/Vendor/VendorTransactionController.php`
  - `src/Controller/Vendor/Metric/VendorMetricController.php`
  - `src/Controller/Vendor/Payout/PayoutController.php`
  - `src/Controller/Vendor/Ledger/VendorSummaryController.php`
  - `src/Controller/Vendor/Payout/PayoutAccountController.php`

## Why this wave
These controllers lived above the service/repository layer but still depended on concrete classes despite existing canonical `*Interface` contracts. This created avoidable concrete coupling in the upper HTTP layer.

## Scope
No DTO shape, route, response payload, or business logic was changed. Only constructor dependency types and imports were narrowed to interfaces.
