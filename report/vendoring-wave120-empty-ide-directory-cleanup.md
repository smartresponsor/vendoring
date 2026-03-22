# Vendoring Wave 120 - Empty IDE Directory Cleanup

- removed the committed empty hidden IDE directory `.ide/` from the cumulative snapshot
- extended `CanonicalIdeRuntimeArtifactContractTest` to forbid an empty hidden `.ide` directory
- extended `tests/bin/idea-module-artifact-smoke.php` to fail if `.ide/` is present

Why this matters:
- `.ide/` as an empty hidden directory was no longer carrying any source-of-truth content after the earlier IDE artifact cleanup waves
- keeping it in the cumulative snapshot would preserve machine-local IDE residue without any operational value
