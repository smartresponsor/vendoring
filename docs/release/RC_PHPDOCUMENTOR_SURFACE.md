# Vendoring RC phpDocumentor Surface

The repository includes a phpDocumentor configuration and a repository-owned generated reference index.

## Current artifacts

- `phpdoc.dist.xml`
- `bin/generate-phpdocumentor-site.php`
- `build/docs/phpdocumentor/index.html`

## Current strategy

At this stage the repository generates a stable reference landing page that documents:
- the code-reference role of the phpDocumentor surface
- the generated OpenAPI contract artifacts
- the release-candidate narrative/operator documentation that surrounds the code reference

This keeps the RC documentation bundle predictable and publishable from CI even before the full `phpdocumentor/phpdocumentor` binary is wired into every consumer runtime.

## Tightening path

Once dependency installation and publishing lanes are fully aligned, the same `phpdoc.dist.xml` can drive the full `phpdocumentor/phpdocumentor` binary and replace the fallback landing page with navigable API documentation.

That follow-up is an improvement in richness, not a reason to mix generated reference output into hand-written Antora narrative pages.
