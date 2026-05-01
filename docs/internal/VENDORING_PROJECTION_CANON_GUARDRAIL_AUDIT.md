# Vendoring Projection Canon Guardrail Audit

## Scope

This wave cleans residual documentation and runtime terminology left after the View-to-Projection migration.
It does not introduce a new runtime layer and does not change repository namespace policy.

## Findings

- Root README and gate/prompt metadata still allowed `Vendor*View.php` as the projection filename pattern.
- API inventory and PHPDoc guidance still described runtime projection payloads as `views`.
- One controller error code still exposed `profile_view_unavailable`, which could reintroduce the old terminology into API-facing behavior.
- `VendorOwnershipController` still referred to an ownership view builder in PHPDoc despite using `VendorOwnershipProjectionBuilderServiceInterface`.

## Changes

- Updated projection filename canon to `Vendor*Projection.php`.
- Updated runtime documentation vocabulary from runtime `views` to runtime `projections` where the term referred to current projection classes.
- Updated the profile update fallback error code to `profile_projection_unavailable`.
- Kept historical audit manifests intact where they document prior renames/deletions, but corrected active canon documents that may guide future generation.

## Non-goals

- No `.commanding/docker` move: that remains operator/tooling scope, not application deploy.
- No namespace migration.
- No repository-wide destructive cleanup.
