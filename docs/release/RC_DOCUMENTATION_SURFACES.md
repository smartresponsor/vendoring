# Vendoring RC Documentation Surfaces

## Current state

The repository exposes a producer-grade documentation contour with a clear separation between:
- Antora narrative entry pages under `docs/modules/ROOT/pages/`
- GitHub-facing repository guidance in `README.md` and release markdown
- generated contract/reference artifacts under `build/docs/` and `build/release/`

## Documentation layers

### Antora producer surface

Repository-owned Antora producer files:
- `docs/antora.yml`
- `docs/modules/ROOT/nav.adoc`
- `docs/modules/ROOT/pages/index.adoc`
- `docs/modules/ROOT/pages/architecture.adoc`
- `docs/modules/ROOT/pages/install.adoc`
- `docs/modules/ROOT/pages/operations.adoc`
- `docs/modules/ROOT/pages/api.adoc`
- `docs/modules/ROOT/pages/reference.adoc`

This repository intentionally publishes content only.
It does not assemble a standalone Antora site, central playbook, or UI/publishing layer.

### OpenAPI/Nelmio surface

Repository-owned generation seam:
- `bin/generate-openapi.php`

Generated artifacts:
- `build/docs/openapi.json`
- `build/docs/openapi.yaml`

Nelmio/Symfony-native browsing seam:
- `config/packages/nelmio_api_doc.yaml`
- `config/packages/vendor_nelmio_api_doc.yaml.dist`
- `config/routes/vendor_nelmio_api_doc.yaml.dist`

### Code-reference surface

Repository-owned generation seam:
- `phpdoc.dist.xml`
- `bin/generate-phpdocumentor-site.php`

Generated artifact:
- `build/docs/phpdocumentor/index.html`

## RC documentation rule

The component should only claim a stronger RC label when runtime/documentation surfaces stay aligned:
- public HTTP contours are described by generated or repository-owned machine-facing contracts
- narrative/operator/release docs explain what is live, what is generated, and what is intentionally deferred
- code-reference artifacts remain separate from hand-written narrative docs
- the Antora producer surface stays minimal and aggregator-friendly

## Current generated evidence

- OpenAPI JSON/YAML under `build/docs/`
- phpDocumentor fallback index under `build/docs/phpdocumentor/`
- RC evidence and release/rollback manifests under `build/release/`
- release-candidate narrative docs under `docs/release/`
- conditional live activation path documented in `docs/release/RC_RUNTIME_ACTIVATION.md`
