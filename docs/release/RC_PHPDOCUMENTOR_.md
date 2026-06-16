# Vendoring RC phpDocumentor Surface

The repository now includes a phpDocumentor configuration and CI-friendly generation seam.

## Current artifacts

- `phpdoc.dist.xml`
- `build/docs/phpdocumentor/index.html`

## Current strategy

At this stage the repository generates a stable placeholder site and keeps the canonical `phpdoc.dist.xml` configuration in-tree.
This is enough for release-candidate evidence because CI can publish a predictable documentation bundle now.

## Next tightening step

Once dependency installation is aligned on the consumer side, the same configuration can be used to run the full `phpdocumentor/phpdocumentor` binary and replace the placeholder HTML with navigable API documentation.
