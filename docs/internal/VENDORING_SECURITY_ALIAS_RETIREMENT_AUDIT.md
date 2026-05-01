# Vendoring Security Alias Retirement Audit

## Scope

Wave G retires the transitional security wrapper and removes empty root-level release/smoke skeleton folders from the active repository surface.

## Findings

- `VendorSecurityService` was a backward-compatible wrapper around `VendorApiKeyServiceInterface`.
- `VendorSecurityServiceInterface` extended `VendorApiKeyServiceInterface` and duplicated the same API-key methods.
- No active source caller used `VendorSecurityServiceInterface`; only the DI alias remained.
- Root-level `.release/` and `.smoke/` contained empty placeholder runner files, not executable application code.

## Canonical result

- Machine credential operations stay under `VendorApiKeyServiceInterface`.
- Security-state projection remains under `VendorSecurityStateProjectionBuilderServiceInterface`.
- Empty deployment/smoke placeholders are removed from root instead of preserved as misleading structure.

## Deferred

- `.commanding/docker/` is intentionally left untouched because it is operator tooling, not application deployment.
- Non-empty deploy assets under `deploy/` remain the canonical application deployment location.
