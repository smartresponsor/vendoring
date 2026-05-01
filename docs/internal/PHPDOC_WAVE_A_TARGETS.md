# Vendoring PHPDoc Wave A Targets

## Objective

Apply deterministic PHPDoc coverage to the first runtime and release-read layer so that phpDocumentor, maintainers, and automated tooling read the same intent from the code.

## Target files

### Ops
- `src/ServiceInterface/Ops/VendorRuntimeStatusProjectionBuilderServiceInterface.php`
- `src/Service/Ops/VendorRuntimeStatusProjectionBuilderService.php`
- `src/ServiceInterface/Ops/VendorReleaseBaselineReaderServiceInterface.php`
- `src/Service/Ops/VendorReleaseBaselineReaderService.php`

### Finance
- `src/ServiceInterface/VendorFinanceRuntimeProjectionBuilderServiceInterface.php`
- `src/Service/VendorFinanceRuntimeProjectionBuilderService.php`

### Statement
- `src/ServiceInterface/Statement/VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface.php`
- `src/Service/Statement/VendorStatementDeliveryRuntimeProjectionBuilderService.php`
- `src/ServiceInterface/Statement/VendorStatementRecipientProviderServiceInterface.php`
- `src/Service/Statement/VendorStatementRecipientProviderService.php`

### Integration
- `src/ServiceInterface/Integration/VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface.php`
- `src/Service/Integration/VendorExternalIntegrationRuntimeProjectionBuilderService.php`

## Acceptance criteria

Each target file should satisfy all of the following:

### Class-level
- declares whether the class is read-side or write-side
- states whether the class is side-effect free
- explains what projection, summary, or aggregation it builds
- names the adjacent surfaces or data sources it combines

### Constructor-level implications
- dependencies should be inferable from the class PHPDoc and method PHPDoc
- no constructor docblock is required unless dependency semantics are ambiguous

### Public method-level
- every public method explains the semantic role of each argument
- every public method explains whether date bounds are inclusive
- every public method explains currency expectations when applicable
- every public method declares the meaning of the returned projection
- every public method avoids generic wording like "gets data" or "builds output"

### Private helper-level
- every helper returning structured arrays must use array-shape PHPDoc
- normalization helpers must document nullable fields explicitly
- readiness/status helpers must declare finite status values whenever practical

## Required array-shape candidates

### VendorRuntimeStatusProjectionBuilderService
- `ownership: array|null`
- `profile: array|null`
- `finance: array<string,mixed>`
- `statementDelivery: array<string,mixed>`
- `externalIntegration: array<string,mixed>`
- `surfaceStatus: array<string,bool>`

### VendorReleaseBaselineReaderService
- `artifactStatus: array<string,bool>`
- `issues: list<string>`
- profile summary helper:

```php
array{
  available: bool,
  completionPercent: ?int,
  readyForPublishing: ?bool,
  nextAction: ?string
}
```

### VendorStatementDeliveryRuntimeProjectionBuilderService
- `ownership: array|null`
- `export: array{path:string, exists:bool, readable:bool}|null`
- `recipients: list<array{tenantId:string, vendorId:string, email:string, currency:string}>`

## Style rules

- prefer business meaning over implementation trivia
- explicitly state when a method is side-effect free
- use `list<T>` where order matters
- use nullable markers instead of prose like "may be absent"
- do not describe legacy wrappers as canonical surfaces
- keep Symfony-oriented namespace boundaries unchanged

## Expected output

Wave A is complete when runtime and release-read files become:
- phpDocumentor-ready
- unambiguous for humans
- stable for machine parsing
- suitable as a base for RC documentation generation
