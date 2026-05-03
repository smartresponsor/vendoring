# Vendoring Wave AT Runtime Preflight Sync

## Scope

This wave synchronizes active runtime/preflight smoke contracts after the structural and namespace cleanup closure waves.

## Changes

- Aligned Nelmio API documentation configuration with the RC API contract.
- Restored the Doctrine split expected by active storage smokes: `user_data` for business data and `app_data` for internal SQLite state.
- Added the lightweight `vendor_bridge.yaml` parameter surface for the internal SQLite DSN.
- Aligned runtime activation smoke with the current native Symfony activation strategy and retired legacy `config/*_runtime.php` shims.
- Added minimal GitHub workflow contracts required by the RC evidence/documentation smokes.
- Narrowed the no-example wording smoke so generated reference files, OpenAPI schema keywords, and guardrail definitions are not false positives.
- Removed active fixture/example wording that was not part of a generated schema or guardrail.

## Production impact

No production PHP service logic was changed. The only production-adjacent changes are configuration files and data fixture wording.
