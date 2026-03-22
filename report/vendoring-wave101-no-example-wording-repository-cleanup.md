# Vendoring Wave 101 — No Example Wording Repository Cleanup

## Scope
- remove repository-level `example only`/`example: canonization` wording from operational manifests and workflow metadata
- add guard coverage so these markers do not reappear in active cumulative slices

## Files changed
- `.deploy/systemd/MANIFEST.md`
- `.commanding/systemd/MANIFEST.md`
- `.github/workflows/consuming.yml`
- `.consuming/.github/workflows/consuming.yml`
- `tests/Unit/Infrastructure/CanonicalNoExampleWordingRepositoryContractTest.php`
- `tests/bin/no-example-wording-repository-smoke.php`
- `tests/bin/smoke.php`
- `composer.json`

## Result
- repository-level example wording markers were removed from operational manifests and workflow dispatch descriptions
- guard coverage now checks this wording slice through both smoke and PHPUnit contract layers
