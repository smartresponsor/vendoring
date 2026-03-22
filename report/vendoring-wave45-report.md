# Vendoring Wave 45 Report

## Scope
- strengthen the first real unit-testable domain slice around ledger posting and vendor settlement
- expose explicit `test:unit` composer workflow
- keep the component Symfony-oriented and minimal

## Changes
- added `tests/Support/Repository/InMemoryLedgerEntryRepository.php`
- added `tests/Unit/Ledger/DoubleEntryServiceTest.php`
- added `tests/Unit/Payout/VendorSettlementCalculatorTest.php`
- updated `composer.json` with explicit `test:unit` script and made `quality` target it directly
- updated `phpunit.xml.dist` with `smoke` and `unit` suites
- updated smoke checks to require `tests/Unit`

## Notes
- new unit tests exercise real application classes without introducing alternate architecture patterns
- no new `src/*/Vendor/*` branch was introduced
- `src/Entity/Vendor/...` remains the only allowed Vendor-named structural exception under `src`
