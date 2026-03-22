# Vendoring Wave 91 — Composer Quality Parity

- Normalized `quality` entries from `composer test:*` / inline `&&` chaining to canonical `@test:*` references.
- Added `ComposerQualityScriptParityTest`.
- Added `tests/bin/composer-quality-parity-smoke.php`.
- Registered `test:composer-quality-parity` and wired it into `quality`.
