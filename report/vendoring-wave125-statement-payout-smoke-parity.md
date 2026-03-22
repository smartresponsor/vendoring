# Vendoring Wave 125 - Statement/Payout Smoke Parity

## Summary
- added `tests/bin/statement-service-smoke.php`
- added `tests/bin/payout-service-smoke.php`
- normalized `test:statement` to `smoke + phpunit`
- normalized `test:payout` to `smoke + phpunit`
- extended master smoke to require both new smoke files

## Reason
`test:statement` and `test:payout` were the remaining domain slices without their own `tests/bin/*-smoke.php` entrypoint, while the active orchestration had already converged on a `smoke + phpunit` pattern almost everywhere else.
