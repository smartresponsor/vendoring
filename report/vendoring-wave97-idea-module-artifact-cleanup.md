# Vendoring Wave 97 Report

## Summary
Removed committed IDE module artifacts from `.idea/` and added guard coverage to prevent their return in cumulative snapshots.

## Changes
- removed `.idea/Canonization.iml`
- removed `.idea/Vendor.iml`
- added `tests/Unit/Infrastructure/CanonicalIdeRuntimeArtifactContractTest.php`
- added `tests/bin/idea-module-artifact-smoke.php`
- updated `tests/bin/smoke.php`
- updated `composer.json`
