# Vendoring Core Service Name Cleanup Audit

## Scope

Wave AC removes the remaining generic core service name from the typed Symfony service layer.

## Findings

- `src/Service/Core/VendorService.php` used a component-level generic class name inside a typed `Core` service folder.
- The mirrored interface used the same generic shape: `VendorServiceInterface`.
- The behavior is core vendor lifecycle orchestration: create/update vendor aggregate state, emit lifecycle events, validate DTOs, and synchronize owner assignment.

## Changes

- `VendorService` -> `VendorCoreService`
- `VendorServiceInterface` -> `VendorCoreServiceInterface`
- Updated the explicit DI alias in `config/component/services.yaml`.

## Canonical result

The Core service folder now contains a service whose class name reflects the capability bucket rather than using a repository-wide generic name.

## Non-goals

- No business logic changes.
- No entity or repository changes.
- No runtime-proof expansion beyond syntax-level file checks.
