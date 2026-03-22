# Vendoring Wave 35 Report

- Scope: command-layer output fidelity for vendor statement delivery flow.
- Change: expanded `finance:send-vendor-statements` console output to retain delivery scope and delivery result details already available after wave32.
- Files touched:
  - `src/Command/Vendor/SendVendorStatementsCommand.php`
- Why: the command already built/exported/mailed a scoped statement, but console output returned only `[vendorId] SENT|FAIL: message`, losing tenant, email, period, currency, attachment state, and PDF path.
- Result: command output now preserves tenant/vendor scope and delivery diagnostics without changing service logic.
