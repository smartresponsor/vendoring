# Vendoring Wave 96 - App Namespace Repository Cleanup

## Summary
- removed legacy `Vendor\\` namespace markers from `ops/policy/config/services_interface.yaml`
- normalized the operational service interface config to canonical `App\\` namespace usage
- added PHPUnit and smoke guards to prevent `Vendor\\` namespace drift from returning in repository config

## Files changed
- `ops/policy/config/services_interface.yaml`
- `tests/Unit/Infrastructure/CanonicalAppNamespaceRepositoryContractTest.php`
- `tests/bin/app-namespace-repository-smoke.php`
- `tests/bin/smoke.php`
- `composer.json`
