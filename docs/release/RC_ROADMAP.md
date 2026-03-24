# Vendoring RC Roadmap

This roadmap turns the repository from “green tests” into a release-candidate surface with visible evidence in CI, runtime, persistence, and documentation.

## Wave A — RC foundation

Focus:
- decompose CI into visible quality lanes
- align composer scripts with those lanes
- align README and release docs with the actual component shape
- keep the repository positioned as a headless/business component first

Deliverables:
- grouped composer quality lanes
- release-candidate workflows for quality, runtime, docs, and aggregate evidence
- release-facing documentation skeleton

## Wave B — transaction vertical runtime

Focus:
- strengthen the `VendorTransaction` contour as the canonical vertical slice
- prove request → controller → manager → policy → repository → Doctrine → database → response

Deliverables:
- richer kernel/runtime tests
- stronger JSON contract assertions
- fresh database boot coverage
- target-like persistence smoke expansion

## Wave C — minimal operator surface

Focus:
- add minimal Symfony Twig/Form operator flows without reframing the repository as a UI-first product

Deliverables:
- list/create/update operator pages for the selected vertical slice
- Bootstrap-based templates
- HTML/form submit smoke tests
- validation rendering tests

## Wave D — OpenAPI and generated docs

Focus:
- expose the public HTTP surface as a formal contract
- generate developer-facing documentation from code metadata

Deliverables:
- Nelmio API Bundle integration
- OpenAPI attributes for request/response surfaces
- DocBlock pass on public HTTP/DTO seams
- phpDocumentor build integration

## Wave E — release evidence

Focus:
- make the repository explainable as a release candidate

Deliverables:
- aggregate CI evidence
- release-facing docs alignment
- explicit definition of what is ready and what remains post-RC
