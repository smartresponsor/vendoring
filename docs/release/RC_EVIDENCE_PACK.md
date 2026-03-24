# Vendoring RC Evidence Pack

This repository now produces a release-facing evidence bundle instead of relying only on green CI output.

## Generated outputs

After `composer docs:build` and `composer docs:rc-evidence`, the repository owns these downloadable artifacts:

- `build/docs/openapi.json`
- `build/docs/openapi.yaml`
- `build/docs/phpdocumentor/index.html`
- `build/release/rc-evidence.json`
- `build/release/rc-evidence.md`

## Why this matters

A release-candidate claim is stronger when it is backed by an inspectable bundle that explains:

- what runtime surfaces are currently proven
- which CI lanes protect those surfaces
- which generated documentation artifacts are available
- which package activations are expected for the live Twig/Form/Nelmio path

## Primary commands

```bash
composer docs:build
composer docs:rc-evidence
composer quality:release-candidate
```

## CI expectation

The `docs`, `runtime`, and `release-candidate` workflows should upload `build/docs/**` and `build/release/**` so the RC state is preserved as a build artifact instead of only as console output.
