# Phase 58 — phpDocumentor regeneration pass

## Purpose

Re-run the repository-owned phpDocumentor generation path after the residual public-entrypoint documentation wave and record the factual result.

## Command

```bash
php bin/generate-phpdocumentor-site.php
```

## Result

The generation pass completed successfully and regenerated:

- `build/docs/phpdocumentor/index.html`

The current artifact remains a **repository-owned placeholder documentation bundle**.
It is stable and reproducible, but it is **not yet a full navigable phpDocumentor API site** produced by the upstream `phpdocumentor/phpdocumentor` package.

## Observed current state

### Working generation path
- `phpdoc.dist.xml` exists
- `bin/generate-phpdocumentor-site.php` runs successfully
- `build/docs/phpdocumentor/index.html` is regenerated without errors

### Current artifact semantics
The generated HTML confirms that the repository is still using a transitional placeholder generation model intended to:
- publish a stable docs bundle in CI
- expose the RC documentation surface
- keep room for later wiring of the full phpDocumentor binary

### Still missing for full phpDocumentor mode
- upstream `phpdocumentor/phpdocumentor` package wired into the repository runtime/tooling path
- full class/member navigation output
- richer package/namespace browsing
- full API-site generation integrated into release evidence flow

## Interpretation

This pass is successful from a **release-process** standpoint:
- docs generation is reproducible
- the artifact path is stable
- the placeholder bundle remains publishable

This pass is not yet the final desired end state from a **documentation richness** standpoint:
- the explainability wave improved source-level PHPDoc coverage substantially
- but repository tooling is still publishing a placeholder surface rather than a full phpDocumentor site

## Recommended next step

Choose one of the following:

1. Keep the current placeholder generator for RC and defer full phpDocumentor wiring to a post-RC documentation/tooling wave.
2. Add and wire the upstream `phpdocumentor/phpdocumentor` package, then run a full generation pass and compare the produced API surface against the current placeholder bundle.

## Acceptance baseline

This phase is complete when:
- the generation command runs successfully
- the generated artifact exists in `build/docs/phpdocumentor/index.html`
- the repository records the result as a factual docs-generation state rather than an assumed capability
