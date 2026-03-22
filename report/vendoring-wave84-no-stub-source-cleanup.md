# Vendoring Wave 84 - No Stub Source Cleanup

## Scope
- removed a stub marker from production source
- added source-level guard against `stub` markers inside `src/`
- wired smoke/composer checks for the new guard

## Files
- src/Service/Payout/PayoutProviderBridge.php
- tests/Unit/Infrastructure/CanonicalNoStubSourceContractTest.php
- tests/bin/no-stub-source-smoke.php
- tests/bin/smoke.php
- composer.json
