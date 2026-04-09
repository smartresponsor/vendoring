# Vendoring

Vendoring is a Symfony-oriented business component for vendor-facing operational flows.
At the current release-candidate hardening stage, the repository is centered on:

- vendor transaction runtime slices
- statement and payout services
- Doctrine-backed persistence contracts
- release-facing CI evidence and canonical owner gates

## Current component shape

This repository is currently strongest as a **headless/backend component**.
It already contains HTTP/controller, service, policy, repository, Doctrine, and runtime-smoke surfaces.
A minimal server-rendered operator surface is already in-tree, and the repository now also emits OpenAPI, phpDocumentor, and RC evidence-pack artifacts as part of the release-candidate baseline.

## Local quality lanes

```bash
composer quality:static
composer quality:contracts
composer quality:runtime
composer quality:persistence
composer quality:api
composer quality:docs
composer quality:release-candidate
```

Full aggregate:

```bash
composer quality
```


## Data storage strategy

Runtime intent is split by data sensitivity and operational role:

- **PostgreSQL** for user/business data (`vendor.dsn`, typically `pgsql://...`)
- **SQLite** for application/runtime local data and deterministic integration flows (`vendor.sqlite_dsn`, typically `sqlite:///%kernel.project_dir%/var/vendor_runtime.sqlite`)

Example environment values:

```bash
VENDOR_DSN=pgsql://app:app@127.0.0.1:5432/vendoring
VENDOR_SQLITE_DSN=sqlite:///%kernel.project_dir%/var/vendor_runtime.sqlite
```

## Release-candidate documentation

- `docs/release/RC_ROADMAP.md`
- `docs/release/RC_CI_LANES.md`
- `docs/release/RC_RUNTIME_SURFACES.md`
- `docs/release/RC_DOCUMENTATION_SURFACES.md`
- `docs/release/RC_BASELINE.md`
- `docs/release/RC_EVIDENCE_PACK.md`

## Antora producer surface

- `docs/antora.yml`
- `docs/modules/ROOT/pages/index.adoc`
- `docs/modules/ROOT/pages/architecture.adoc`
- `docs/modules/ROOT/pages/install.adoc`
- `docs/modules/ROOT/pages/operations.adoc`
- `docs/modules/ROOT/pages/api.adoc`
- `docs/modules/ROOT/pages/reference.adoc`

This repository provides documentation content as an Antora producer only. Central site assembly, playbooks, UI, and publishing logic remain outside this repository.

## Current note

The repository intentionally does **not** present itself as a finished Twig/Form admin product yet.
The next strengthening waves are aimed at making that operator surface minimal, testable, and CI-visible without turning the component into a separate UI-first application.


## Documentation artifacts

Generated RC evidence includes OpenAPI, phpDocumentor, and release-facing manifest artifacts under `build/docs/` and `build/release/`.
