# Vendoring RC CI Lanes

The CI layer should show the maturity of the component volumetrically rather than as one opaque test run.

## Canon lane

Purpose:
- owner rules
- root protocol cleanup
- composer script parity
- repository hygiene

Primary signals:
- `.github/workflows/gate.yml`
- `.github/workflows/vendor-canon.yml`
- `composer quality:contracts`

## Static lane

Purpose:
- syntax, formatting, static analysis

Primary signals:
- `composer quality:static`

## Runtime lane

Purpose:
- Symfony boot
- DI and entrypoint viability
- statement, payout, and transaction runtime slices

Primary signals:
- `composer quality:runtime`

## Persistence lane

Purpose:
- Doctrine mapping
- migration/schema parity
- sqlite-backed integration seams

Primary signals:
- `composer quality:persistence`

## API lane

Purpose:
- transaction policy, error surface, identity/idempotency, JSON contract

Primary signals:
- `composer quality:api`

## Documentation lane

Purpose:
- RC docs presence and coherence
- OpenAPI artifact generation
- phpDocumentor configuration and generated site placeholder
- release manifest generation under `build/release/`
- rollback manifest generation under `build/release/`
- readiness for full Nelmio/phpDocumentor binary wiring

Primary signals:
- `composer quality:docs`

## Aggregate release-candidate lane

Purpose:
- one visible umbrella signal for RC hardening

Primary signals:
- `composer quality:release-candidate`
- uploaded artifacts from `build/docs/` and `build/release/`
