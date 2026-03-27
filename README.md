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

## PostgreSQL integration checks (local + Docker)

The integration lane includes a PostgreSQL test (`VendorTransactionPostgresIntegrationTest`) that is enabled when `VENDOR_TEST_POSTGRES_DSN` is provided.

Local PostgreSQL example:

```bash
export VENDOR_TEST_POSTGRES_DSN='postgresql://postgres:postgres@127.0.0.1:5432/vendoring_test?serverVersion=16&charset=utf8'
composer test:transaction-postgres-integration
```

Docker PostgreSQL example:

```bash
export VENDOR_TEST_POSTGRES_DSN='postgresql://postgres:postgres@postgres:5432/vendoring_test?serverVersion=16&charset=utf8'
composer test:transaction-postgres-integration
```

If `VENDOR_TEST_POSTGRES_DSN` is not set (or `pdo_pgsql` is missing), the PostgreSQL integration test is skipped automatically.

## Release-candidate documentation

- `docs/release/RC_ROADMAP.md`
- `docs/release/RC_CI_LANES.md`
- `docs/release/RC_RUNTIME_SURFACES.md`
- `docs/release/RC_DOCUMENTATION_SURFACES.md`
- `docs/release/RC_BASELINE.md`
- `docs/release/RC_EVIDENCE_PACK.md`

## Current note

The repository intentionally does **not** present itself as a finished Twig/Form admin product yet.
The next strengthening waves are aimed at making that operator surface minimal, testable, and CI-visible without turning the component into a separate UI-first application.


## Documentation artifacts

Generated RC evidence includes OpenAPI, phpDocumentor, and release-facing manifest artifacts under `build/docs/` and `build/release/`.
