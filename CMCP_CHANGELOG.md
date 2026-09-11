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
