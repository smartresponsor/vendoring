# Vendoring command fallback concrete service cleanup audit

Wave V fixes a runtime break introduced by interface-first command typing cleanup.

## Finding

Several Symfony console commands correctly typed constructor dependencies against command service interfaces, but their local fallback factories still attempted to instantiate interfaces directly. That is invalid PHP and breaks command construction whenever the optional dependency is not injected by the container.

Affected fallback factories were found in:

- `VendorPayoutCreateCommand`
- `VendorPayoutProcessCommand`
- `VendorCategoryReviewAssignCommand`
- `VendorRuntimeStatusCommand`
- `VendorSendVendorStatementsCommand`

## Change

- Kept constructor and property types on interfaces.
- Switched local fallback factory instantiation to concrete services:
  - `VendorCommandJsonEncoderService`
  - `VendorCommandResultEmitterService`
  - `VendorCommandJsonFileWriterService`
  - `VendorCommandJsonArtifactWriterService`
- Removed now-unused command imports for concrete-uninstantiable interfaces.

## Boundary

This wave does not change command names, service aliases, output schema, or business behavior. It only restores valid local fallback wiring while preserving interface-based consumer typing.

## Verification

`php -l` passed for all five changed command files in the patch workspace.
