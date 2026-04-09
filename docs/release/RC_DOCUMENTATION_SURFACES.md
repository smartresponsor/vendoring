# Vendoring RC Documentation Surfaces

## Current state

The repository now exposes generated documentation evidence for the strongest HTTP contour and keeps Symfony/Nelmio/phpDocumentor integration seams in-tree.

## Near-term documentation goals

### OpenAPI/Nelmio

Planned use:
- document public HTTP routes
- define request/response schemas
- make error surfaces explicit
- expose a browsable API-doc route

### DocBlock discipline

Planned use:
- explain DTOs, controllers, and service entry points
- provide developer-readable semantics beyond route metadata
- support future generated documentation

### phpDocumentor

Planned use:
- produce navigable engineering documentation artifacts
- make RC evidence downloadable from CI artifacts

## RC documentation rule

The component should only claim a stronger RC label when runtime/documentation surfaces are aligned:
- public HTTP contours are described
- CI can build documentation artifacts or at least validate the documentation skeleton
- release docs explain what is currently ready and what is intentionally deferred


## Current generated evidence

- OpenAPI JSON/YAML under `build/docs/`
- phpDocumentor configuration in `phpdoc.dist.xml`
- generated placeholder phpDocumentor site under `build/docs/phpdocumentor/`
- RC evidence manifest files under `build/release/`
- Nelmio integration scaffolding as `.dist` files under `config/`

- `docs/release/RC_RUNTIME_ACTIVATION.md` documents the conditional live activation path for Twig/Form/Nelmio.
