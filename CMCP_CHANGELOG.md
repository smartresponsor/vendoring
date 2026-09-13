# CMCP Orchestration Journal

## engine-20260911154044-vendoring-5849e1

### Baseline

- Workspace: `D:\PhpstormProjects\www\Vendoring`.
- Branch: `release/vendoring-local-head-20260904`.
- Read `AGENTS.md`, `README.md`, `composer.json`, local `.gating` contracts, Git diff, and the affected service/config/test surfaces.
- Read Canonization Canon032, Canon033, Canon034, Canon035 and the architecture guard matrix.
- Compared the modified consumer-local `.gating` files with the current read-only `Gating` repository; they match the current Gating implementations/calibration coverage.

### RC-critical workstream

- `qa:canon` exposed a service/interface mirror violation for `VendorAttachmentOwnerPurgeServiceInterface`.
- Added `VendorAttachmentOwnerPurgeService` as the canonical default mirror implementation and switched DI/unit callers to it.
- The optional Attaching bridge remains an alternate implementation of the same contract.
- `NullVendorAttachmentOwnerPurgeService` is retained because destructive operations are forbidden for this task; it is no longer the configured default.

### Canon mapping

- Canon032: standalone bundle registration detection must support the canonical `app/Kernel.php` surface.
- Canon033: development/production manifest identity parity applies when both manifests exist; this bounded change does not invent a production manifest.
- Canon034: generated/local state such as PHPUnit cache is not product source.
- Canon035: container-reuse enforcement must inspect the canonical `app/Kernel.php` surface.
- Service/interface mirror: `Vendor*ServiceInterface` now has a same-role `Vendor*Service` implementation.

### Gates

- `report:mirror-enforcer`
- `qa:canon`
- `composer validate`
- targeted unit tests for the affected service surface
- broader static/runtime gates in subsequent iterations

### 2026-09-13 RC closure continuation

- Re-read the active Vendoring runtime/config/test surfaces plus mandatory helper contours: Objecting, Cruding, Viewing, Interfacing; Canonization and Gating remained read-only references.
- Added Canon022 and Canon043 to the target-to-canon mapping. Canon022 requires the standalone Symfony baseline (`Collectioning`, `Cruding`, EasyAdmin, `Interfacing`, `Objecting`, `Tabling`, `Viewing`); Canon043 requires local first-party development dependencies at exact `dev-master`.
- Kept generic CRUD routing owned by Cruding. Restored only explicit Vendoring business/operator delivery through native Symfony routes and route-facing services.
- Restored canonical same-role service mirrors for ownership write request resolution, ownership writes, and the default profile attachment resolver.
- Reconciled the standalone Composer contour with `Collectioning`/`Tabling` and exact `dev-master` first-party path dependencies; lock validation passes.
- Reconciled stale config guard/drift expectations with the repository's current RC runtime activation contract instead of recreating retired runtime config files.
- Restored explicit transaction, ownership, finance, payout-account, payout, statement/export, and transaction-operator runtime delivery while preserving the zero-generic-controller boundary.
- Aligned API-key active lookup with Objecting-owned embedded state mapping and aligned transaction amount transport serialization to canonical scale=2.
- Unified finance validation responses with the shared Vendoring `errorCode` envelope; updated stale textual idempotency/canary test assumptions without weakening production semantics.
- Isolated the canary smoke from repository-wide stale circuit-breaker state.

### Canon mapping — current

- Canon022: standalone Symfony app directly requires `Collectioning`, `Cruding`, EasyAdmin, `Interfacing`, `Objecting`, `Tabling`, and `Viewing`.
- Canon032: Vendoring bundle registration remains native through `App\\Vendoring\\VendoringBundle`.
- Canon033: Composer package/type/bundle identity remains `vendoring/vendor`, `symfony-bundle`, `App\\Vendoring\\VendoringBundle`.
- Canon034: generated/local runtime state remains excluded from product-source decisions; no generated state was promoted into source.
- Canon035: Symfony container reuse is preserved through autowiring/aliases and explicit route-facing service tags rather than manual container construction.
- Canon043: local first-party path dependencies are exact `dev-master`.
- Cruding boundary: generic CRUD route/controller mechanics remain outside Vendoring; Vendoring owns only business/operator route delivery.
- Objecting boundary: system/state fields are consumed through Objecting semantics; Vendoring does not duplicate those field primitives.

### RC evidence — current

- `quality:static` PASS: 877 PHP files linted; PHPStan 825 files, 0 errors.
- `quality:contracts` PASS: unit/DI/repository/entrypoint/entity/route contracts green; unit suite 318 tests / 5090 assertions.
- Integration suite proven in bounded partitions because one monolithic Console MCP invocation exceeds its execution window: API-query 8/8, transaction-kernel 4/4, remaining integration 11/11 = 23/23 integration tests PASS.
- `smoke:runtime` PASS (`fresh db boot smoke passed`).
- `quality:api` PASS.
- `quality:persistence` PASS.
- `quality:production-hardening` PASS.
- `quality:docs` PASS.
- `qa:canon` PASS, including canonical structure, PSR-4, mirror, config guard/drift, and PHP surface.
- `composer validate --strict --check-lock` PASS.
- No known RC code/test blocker remains at this checkpoint; Git provenance and publication remain separate closure work.
