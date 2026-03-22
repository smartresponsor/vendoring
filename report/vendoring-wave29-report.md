# Vendoring wave29 report

- Fixed live currency scope-loss in `VendorSummaryController`.
- Added optional `currency` query support and propagated it into `LedgerEntryRepositoryInterface::sumByAccount(...)`.
- Summary response now echoes the effective `currency` field alongside balances.
