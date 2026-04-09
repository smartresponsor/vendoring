# Vendoring Known Non-Blockers

## Purpose
This document tracks issues that do not prevent Release Candidate issuance but should be reviewed after RC stabilization.

## Rules
A non-blocker must not:
- break machine-facing contracts
- weaken permission safety
- invalidate statement delivery correctness
- break null-safe ownership runtime behavior
- reduce release reproducibility

## How to use
- register only issues that are explicitly accepted as post-RC work
- keep each entry concrete and scoped
- do not place blockers here to avoid release drift
- remove entries once they are either resolved or promoted to a real blocker

## Entries

### NNB-001
- Title: Central Antora assembly remains external
- Area: Documentation ecosystem integration
- Description: This repository now exposes an Antora-compatible producer surface, but the central aggregator playbook, UI, and site assembly remain outside the component repository.
- Why not a blocker: Producer readiness is the repository-owned contract; central aggregation is intentionally outside this component boundary.
- Recommended follow-up: Connect the producer surface from the external central documentation aggregator and validate navigation there.
- Target milestone: Post-RC documentation ecosystem wiring

### NNB-002
- Title: Full phpDocumentor binary publishing is still pending
- Area: Generated reference documentation richness
- Description: The repository publishes a stable generated reference landing page under `build/docs/phpdocumentor/`, while full binary-driven navigable phpDocumentor output is deferred.
- Why not a blocker: RC already exposes stable narrative docs, OpenAPI artifacts, and a publishable code-reference entry point.
- Recommended follow-up: Wire the full `phpdocumentor/phpdocumentor` generation lane when consumer/runtime dependency alignment is finalized.
- Target milestone: Post-RC reference-surface enrichment

### NNB-003
- Title: Live Nelmio browsing route is not universally activated
- Area: API browsing surface
- Description: Nelmio/OpenAPI browsing configuration is present in-tree, but some browsing routes remain intentionally activation-gated through repository-owned `.dist` scaffolding.
- Why not a blocker: Machine-facing OpenAPI artifacts are already generated and publishable from CI.
- Recommended follow-up: Enable the live Symfony/Nelmio browsing route in environments where dependency installation and runtime exposure policy are aligned.
- Target milestone: Post-RC API browsing activation
