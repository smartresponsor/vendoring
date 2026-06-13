# Vendoring Wave 13 — Cruding stack alignment

## Scope

Current slice: `www-clean-20260611-151149.zip`.

This patch intentionally reads the current `Cruding`, `Viewing`, and `Interfacing` stack before changing `Vendoring`.

## Stack contract observed in the current slice

### Cruding

Observed files:

- `Cruding/src/Service/Crud/Entrypoint/CrudEntrypointClassNameResolver.php`
- `Cruding/src/Service/Crud/Entrypoint/AbstractCrudEntrypointService.php`
- `Cruding/src/Dto/Crud/Entrypoint/CrudEntrypointContext.php`
- `Cruding/src/Dto/Crud/Entrypoint/CrudEntrypointResult.php`
- `Cruding/src/Value/Surface/CrudSurfaceContract.php`
- `Cruding/docs/cruding/cruding-entrypoint-contract-finalization.md`
- `Cruding/docs/cruding/cruding-wave46-interfacing-provider-surface.md`

Relevant contract:

- URI-derived entrypoints are `App\Vendoring\Service\Http\...`.
- Entrypoints may be small, per-operation classes.
- Supported hooks are `get`, `post`, `put`, `patch`, `delete`.
- Entrypoints may return `CrudSurfaceContract`.
- Cruding stops at a neutral surface contract and does not own browser rendering.
- No per-resource mega-service is required.

### Viewing

Observed files:

- `Viewing/docs/canon/viewing-canon.adoc`
- `Viewing/docs/migration/cruding-to-viewing.adoc`
- `Viewing/src/Service/View/ViewPayloadNormalizer.php`
- `Viewing/src/Value/View/ViewPayload.php`

Relevant contract:

- Viewing owns render decision.
- Producers must not call `render()` or `renderView()`.
- Producers may return neutral View Payload arrays or surface-renderable objects.
- Viewing structurally accepts objects with `toTemplateContext()` and `toFallbackData()`.

### Interfacing

Observed files:

- `Interfacing/src/Contract/Surface/InterfaceSurfaceRenderableInterface.php`
- `Interfacing/src/Contract/Template/InterfaceLocationPayload.php`
- `Interfacing/docs/interfacing-canonical-crud-directory.md`

Relevant contract:

- Interfacing is passive template/shell/layout ownership.
- Producer components should not bind directly to Interfacing interfaces.
- Viewing may consume Interfacing templates through its fallback chain.

## Vendoring alignment applied

### Added HTTP entrypoint surface

All route-map service targets under `config/platform/routes/**` now have physical classes under:

- `src/Service/Http/Vendor/**`

These classes use the route-map namespace expected by Cruding:

- `App\Vendoring\Service\Http\Vendor\...`

They extend:

- `App\Vendoring\Service\Http\Vendor\AbstractVendorCrudRouteService`

The abstract base extends:

- `App\Cruding\Service\Crud\Entrypoint\AbstractCrudEntrypointService`

### Added form type surface

All route-map form type targets now have physical classes under:

- `src/Form/Vendor/**`

These classes use:

- `App\Vendoring\Form\Vendor\...`

and extend Symfony `AbstractType`.

### Read/write behavior

Implemented read candidates:

- `vendor.index`
- `vendor.show_id`
- `vendor.show_slug`
- `vendor.attachment.document.index`
- `vendor.attachment.document.show_id`
- `vendor.attachment.document.show_slug`
- `vendor.attachment.media.index`
- `vendor.attachment.media.show_id`
- `vendor.attachment.media.show_slug`

These return `CrudSurfaceContract` with:

- `status: read_route_ready`
- `persistence: quarantined`
- `mutationAllowed: false`

All mutation/domain-blocked routes return `CrudSurfaceContract` with:

- `status: route_blocked`
- `persistence: quarantined`
- `mutationAllowed: false`

### Zero-controller rule

No controller files were added.

Vendoring now keeps the HTTP entrypoints as:

- `Vendor*Service`

and form entrypoints as:

- `Vendor*Type`

The only support base is:

- `AbstractVendorCrudRouteService`

### Interfacing boundary

Vendoring HTTP services do not import `App\Interfacing\*`.

The response contract records:

- `interfacingDependency: not-bound-inside-vendoring`

## Important non-goals

This wave does not restore persistence.

Still quarantined:

- `Vendor*Entity`
- `Vendor*Repository`
- write-side persistence
- payouts/commissions/onboarding mutations
- attachment mutation

This wave does not add controllers.


## Wave 14 update — entity-to-template coverage criterion

Wave 13 made the Cruding route targets physical and zero-controller, but it does **not** yet prove the full business chain from entity to template.

New audit report:

- `docs/internal/VENDORING_ENTITY_TO_TEMPLATE_COVERAGE_AUDIT.md`
- `delivery/audit/vendoring-wave14-entity-to-template-coverage.json`
- `tools/qa/VendoringEntityToTemplateCoverageAudit.php`

New criterion:

```text
Vendor*Entity
  -> Vendor*Repository / Vendor*RepositoryInterface
  -> Vendor*Service
  -> Vendor*Type when mutation/form operation exists
  -> Viewing payload contract
  -> explicit template candidate or documented Viewing fallback
```

Current conclusion: Wave 13 route surface is Cruding-compatible, but the broader entity graph is **not** fully covered by route/form/template policy yet. The next repair should classify every `Vendor*Entity` as one of: `routable_resource`, `embedded_record`, `workflow_state`, `ledger_record`, `security_internal`, or `retired_candidate`.
