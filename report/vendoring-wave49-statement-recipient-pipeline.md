# Vendoring Wave 49 — statement recipient pipeline hardening

## What changed
- Removed the hard-coded recipient stub from `SendVendorStatementsCommand`.
- Introduced `VendorStatementRecipientProviderInterface` and a default null-safe provider.
- Added explicit CLI options for single-recipient execution:
  - `--tenant-id`
  - `--vendor-id`
  - `--email`
  - `--currency`
  - `--from`
  - `--to`
  - `--period-label`
- Added unit coverage for provider mode, CLI mode, and no-recipient mode.
- Added controller coverage for `VendorStatementController` request validation and payload flow.
- Registered `test:statement-command` in Composer scripts and smoke checks.

## Why this wave matters
Before this wave, the monthly statement command depended on a hard-coded in-method recipient array and a comment describing a future repository integration. That made the command non-canonical and non-extendable.

After this wave, the statement send pipeline is structured around an explicit recipient contract. The default implementation is intentionally empty rather than deceptive. Real recipient sourcing can now be attached through a concrete provider without rewriting the command.
