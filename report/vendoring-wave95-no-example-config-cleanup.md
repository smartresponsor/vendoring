# Vendoring wave 95 — no example config cleanup

## Summary
- removed active example-domain markers from operational policy config
- removed residual `service example` wording from services interface config
- added repository-level guard against `example.com` active config markers

## Files changed
- `ops/policy/config/crm.yaml`
- `ops/policy/config/shadow.yaml`
- `ops/policy/config/api_v1_cors.yaml`
- `ops/policy/config/services_interface.yaml`
- `tests/Unit/Infrastructure/CanonicalNoExampleConfigContractTest.php`
- `tests/bin/no-example-config-smoke.php`
- `tests/bin/smoke.php`
- `composer.json`
