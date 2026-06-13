# Vendoring Documentation Canon

## Purpose

This document defines the documentation contract for the Vendoring component.

Documentation must keep the current runtime architecture explicit:

```text
Vendoring = zero-controller / zero-route component
Cruding   = URI grammar owner
Vendoring = canonical App\Vendoring\Service\Http\Vendor\... service owner
```

## Documentation layers

Vendoring uses four distinct documentation layers:

1. **Route-map / API documentation**
   - Cruding grammar and route-map keys
   - request/response contracts
   - error contracts
   - auth/security expectations

2. **Static code documentation**
   - classes
   - public methods
   - service boundaries
   - invariants
   - side effects

3. **Behavioral scenario documentation**
   - business flows
   - expected state changes
   - projection/runtime expectations
   - failure modes

4. **Operational/release documentation**
   - runtime services
   - route-map registry coverage
   - release baseline expectations
   - RC readiness and gaps

These layers are complementary and must not be collapsed into one generic style.

## Runtime entrypoint canon

Vendoring must not document component-owned Symfony controllers as active runtime entrypoints.

Forbidden active documentation targets:

- `src/Controller/`
- `src/ControllerTrait/`
- the retired component route file
- retired controller namespace classes
- Symfony `#[Route]` attributes inside Vendoring source

Required active documentation targets:

- `config/platform/routes.platform.yaml`
- `config/platform/routes.crud.yaml`
- `config/platform/routes.business.yaml`
- `config/platform/routes/**/*.yaml`
- `src/Service/Http/Vendor/.../*Service.php`
- `src/Form/Vendor/.../*Type.php`

## Layer 3 structure and naming canon for machine agents

Vendoring uses literal Layer 3 contracts for class-type folders frequently edited by agents.

The following folders are locked to one direct child folder named `Vendor` when present:

- `src/Event/`
- `src/EventInterface/`
- `src/Policy/`
- `src/PolicyInterface/`
- `src/Repository/`
- `src/RepositoryInterface/`
- `src/Projection/`

Canonical HTTP runtime services are an exception to the Vendoring namespace because Cruding FQCN convention expects the host-level Symfony namespace:

```text
src/Service/Http/Vendor/.../*Service.php
namespace App\Vendoring\Service\Http\Vendor\...
```

Form Types used by the route-map follow:

```text
src/Form/Vendor/.../*Type.php
namespace App\Vendoring\Form\Vendor\...
```

Do not create `Ledger`, `Payout`, `Ops`, `Finance`, `Statement`, `Integration`, `Metric`, `Dev`, `VendorPayoutEntity`, or any other capability bucket inside strict `Vendor`-only type folders.

`src/Security/` is forbidden as a mixed security bucket. Security classes must be sorted by type. Autowired security services belong in `src/Service/Security/` and their contracts belong in `src/ServiceInterface/Security/`.

See `docs/internal/LAYER3_STRUCTURE_NAMING_CANON.md` for the complete literal contract.

## Required documentation targets

- route-map registry files under `config/platform/`
- canonical `App\Vendoring\Service\Http\Vendor\...` runtime services
- canonical `App\Vendoring\Form\Vendor\...` form types
- repository and policy contracts
- release evidence and smoke commands

## Required smoke references

```bash
php tools/smoke/vendoring-zero-controller-audit.php
php tests/bin/vendoring-zero-controller-hardening-smoke.php
php tests/bin/vendor-route-map-coverage-smoke.php
```
