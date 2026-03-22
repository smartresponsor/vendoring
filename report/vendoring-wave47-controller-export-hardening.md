# Vendoring Wave 47 — Controller export hardening

## Scope
- harden statement export controller against unreadable export files
- remove suppression-based file read
- add controller-level unit coverage for payout and statement export entrypoints
- keep work cumulative on top of wave 46

## Facts addressed
- `src/Controller/Statement/VendorStatementExportController.php` used `@file_get_contents($path)`
- controller also contained a leftover comment `from previous drop (assumed)`
- controller-level HTTP entrypoints had no direct unit coverage
- test suite still had one suppression cleanup call via `@unlink`

## Changes
- replaced suppression-based PDF read with explicit `is_file` / `is_readable` / `file_get_contents() === false` handling
- return `500 statement_export_unreadable` with path context when exported file is not readable
- added support fakes for statement export service contracts
- added unit tests for:
  - `VendorStatementExportController`
  - `PayoutController`
- removed `@unlink` suppression from `VendorStatementServiceTest`
- added composer script `test:controller`
- included controller tests in `quality`

## Validation performed
- PHP lint on changed files: green
- smoke script: green
- full syntax pass over `src/` and `tests/`: green

## Next likely step
- inspect `StatementMailerService` and remaining controller/request flows for exception/logging consistency and absent contract coverage
