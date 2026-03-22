# Vendoring / Vendor — Wave 02

Active base: cumulative snapshot from wave01.

## Scope
Structural cleanup only. No production PHP files under `src/` were modified.

## Changes
- Removed competing non-canonical code tree:
  - `vendoring_repo_winner/`

## Why this was removed
`vendoring_repo_winner/src/Event/Vendor/Payout/PayoutProcessedEvent.php` was a production-like PHP class outside the canonical production root `src/`.
That creates a competing tree and violates the protocol constraint that runtime code must live only under `src/` with namespace `App\` mapped to `src/`.

## Notes
- `docs/legacy/vendor-port` and `docs/legacy/vendor-repository-adapter` still contain Port/Adapter legacy material, but they are documentation artifacts under `docs/legacy`, not active production code.
- No inference was made from older slices. Work was based only on the wave01 cumulative snapshot.
