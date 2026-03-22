# Vendoring wave22 report

## Scope
- Active base: cumulative snapshot wave21
- Focus: live semantic mismatch in `src/Service/Vendor/Payout` and supporting `src/Repository/Vendor/Ledger` contract

## Findings
- `VendorSettlementCalculator::netForPeriod()` accepted `tenantId`, `vendorId`, `from`, `to`, and `currency` but ignored all filters except `tenantId`.
- `LedgerEntryRepositoryInterface::sumByAccount()` and `LedgerEntryRepository::sumByAccount()` supported tenant, period, and vendor filters, but not currency filtering.

## Changes
- Extended `LedgerEntryRepositoryInterface::sumByAccount()` with optional `?string $currency = null`.
- Extended `LedgerEntryRepository::sumByAccount()` to apply `currency = :currency` when provided.
- Updated `VendorSettlementCalculator::netForPeriod()` to pass `from`, `to`, `vendorId`, and `currency` into the ledger summary query.

## Result
- Settlement calculation now honors its full public contract instead of silently returning an unfiltered tenant-wide `VENDOR_PAYABLE` aggregate.
