# Vendoring ownership write quarantine audit

## Decision

The current slice does not contain `src/Entity/`, `src/Repository/`, `src/Projection/`, `src/Policy`, or their matching interface folders, while the ownership write layer references `App\Vendoring\Entity\Vendor\*Entity` classes directly.

The ownership write layer is therefore not a safe runtime surface in this slice. It is quarantined as a dead write-side remnant instead of being treated as a valid Doctrine model.

## Removed from runtime

- `src/DTO/Ownership/*`
- `src/Service/Ownership/VendorOwnershipWriteService.php`
- `src/Service/Ownership/VendorOwnershipWriteRequestResolverService.php`
- `src/ServiceInterface/Ownership/VendorOwnershipWriteServiceInterface.php`
- `src/ServiceInterface/Ownership/VendorOwnershipWriteRequestResolverServiceInterface.php`
- `src/DataFixtures/VendorOwnershipDemoFixture.php`

## Service graph

The aliases for the ownership write service and write request resolver were removed from `config/component/services.yaml`.

The read-side ownership projection service was not removed in this wave because other runtime projection builders still depend on `VendorOwnershipProjectionBuilderServiceInterface`.

## Next wave candidate

The next safe step is a broader Vendoring persistence-surface audit: either restore the missing entity/repository/projection contracts as a coherent Symfony component, or remove all remaining services that still import missing `App\Vendoring\Entity\Vendor\*Entity` classes.
