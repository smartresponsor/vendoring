# Vendoring structure cleanup audit

Scope: normalize deployment into deploy/ and source naming by type-oriented layer.

Applied:
- Root compose.yaml removed; use deploy/docker/compose.yaml.
- Projection payload classes renamed from *View to *Projection.
- References in src/config/tests were updated in this working slice.

Remaining:
- .commanding/docker is operator tooling and was not moved in this pass.
- Service classes previously named *ViewBuilderService were later renamed to *ProjectionBuilderService so the service names mirror the projection layer terminology.
