# Vendoring Projection Builder Canonization Audit

## Scope

This pass continues the root/source cleanup from the current Vendoring slice and focuses on the runtime read-model builder layer.

## Finding

The previous structure mixed two names for the same concept:

- `src/Projection/.../*Projection.php` for immutable/read-model payloads.
- `src/Service/.../*ViewBuilderService.php` and `src/ServiceInterface/.../*ViewBuilderServiceInterface.php` for services that build those projections.

That mismatch made the source tree harder to audit because the service class name and file name could disagree, and the folder intent was not visible from the class suffix.

## Canonical decision applied

Read-model payloads stay in `src/Projection`.

Services that construct those payloads now use the explicit `ProjectionBuilderService` suffix.

Interfaces mirror the same service name under `src/ServiceInterface`.

## Files renamed

- `VendorFinanceRuntimeViewBuilderService*` -> `VendorFinanceRuntimeProjectionBuilderService*`
- `VendorExternalIntegrationRuntimeViewBuilderService*` -> `VendorExternalIntegrationRuntimeProjectionBuilderService*`
- `VendorOwnershipViewBuilderService*` -> `VendorOwnershipProjectionBuilderService*`
- `VendorProfileViewBuilderService*` -> `VendorProfileProjectionBuilderService*`
- `VendorRuntimeStatusViewBuilderService*` -> `VendorRuntimeStatusProjectionBuilderService*`
- `VendorSecurityStateViewBuilderService*` -> `VendorSecurityStateProjectionBuilderService*`
- `VendorStatementDeliveryRuntimeViewBuilderService*` -> `VendorStatementDeliveryRuntimeProjectionBuilderService*`

## Remaining cleanup candidates

- Legacy local variable names such as `$runtimeViewBuilder` may remain harmless inside methods, but the injected type contracts are now canonical.
- Some generic infrastructure interfaces intentionally have broader names than their file-backed implementations, for example file-backed circuit breaker and metric collector services. Those should be reviewed in a separate mirror-contract pass rather than mixed with projection builder renaming.
