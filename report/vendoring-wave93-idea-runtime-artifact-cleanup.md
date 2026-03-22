# Vendoring Wave 93 — IDE runtime artifact cleanup

## Changes
- removed committed IDE runtime artifact `.idea/workspace.xml`
- added `tests/Unit/Infrastructure/CanonicalIdeRuntimeArtifactContractTest.php`
- added `tests/bin/idea-runtime-artifact-smoke.php`
- extended `composer.json` with `test:idea-runtime-artifact`
- extended `quality` with `@test:idea-runtime-artifact`
- extended `tests/bin/smoke.php` to require the IDE runtime artifact guard

## Why
`.idea/` is allowed as a root dot-folder in the component canon, but `.idea/workspace.xml` is machine-local IDE runtime state, not canonical repository source.
