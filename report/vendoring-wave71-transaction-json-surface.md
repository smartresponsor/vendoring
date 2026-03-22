# Vendoring Wave 71 — Transaction JSON Surface

## Scope
- bounded malformed JSON handling for transaction controller create/update entrypoints
- stable `400` error code surface instead of uncaught request decoding exception
- smoke and controller test coverage

## Changes
- added `VendorTransactionErrorCode::MALFORMED_JSON`
- `VendorTransactionController::create()` now catches `JsonException` from `Request::toArray()` and returns `400`
- `VendorTransactionController::updateStatus()` now catches `JsonException` from `Request::toArray()` and returns `400`
- added controller tests for malformed JSON in both create and update flows
- added `tests/bin/transaction-json-surface-smoke.php`
- added composer script `test:transaction-json`

## Result
Transaction HTTP entrypoints no longer leak request-decoding exceptions when malformed JSON reaches the controller.
