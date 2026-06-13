# Vendoring PHPDoc Wave A Patch Guide

## Purpose

This guide contains the exact PHPDoc intent that should be applied to the first runtime and release-read files during the documentation-hardening wave.

The goal is to keep the edits architecture-first and avoid low-value format churn.

## 1. `src/ServiceInterface/Ops/VendorRuntimeStatusProjectionBuilderServiceInterface.php`

### Class intent

```php
/**
 * Read-side contract for aggregating vendor runtime surfaces into one ops-facing projection.
 *
 * Implementations may read profile, ownership, finance, statement, and integration projections,
 * but they must not mutate domain state while building the projection.
 */
```

### Method intent

```php
/**
 * Build a runtime status projection for one tenant/vendor scope.
 *
 * @param string      $tenantId Canonical tenant scope for nested runtime readers.
 * @param string      $vendorId Vendor identifier as received by runtime and API surfaces.
 * @param string|null $from     Optional inclusive period start for finance and statement sections.
 * @param string|null $to       Optional inclusive period end for finance and statement sections.
 * @param string      $currency Canonical currency code for finance-facing sections.
 *
 * @return VendorRuntimeStatusProjection Immutable projection combining ownership, profile, finance,
 *                                 statement delivery, external integration, and surface readiness.
 */
```

## 2. `src/Service/Ops/VendorRuntimeStatusProjectionBuilderService.php`

### Class intent

```php
/**
 * Read-side aggregator that assembles a release-facing vendor runtime projection.
 *
 * The builder combines ownership, profile, finance, statement-delivery, and integration
 * surfaces into a single ops/admin-friendly projection without mutating domain state.
 */
```

### Method intent

```php
/**
 * Build a runtime status projection for the given tenant/vendor scope.
 *
 * Date bounds are forwarded unchanged to finance and statement surfaces when present.
 * Empty bounds are normalized to empty strings only for downstream builders that expect
 * scalar values.
 *
 * @param string      $tenantId Canonical tenant scope.
 * @param string      $vendorId Vendor identifier exposed by API and runtime surfaces.
 * @param string|null $from     Optional inclusive period start for finance and statement projections.
 * @param string|null $to       Optional inclusive period end for finance and statement projections.
 * @param string      $currency Canonical currency code for finance-facing sections.
 *
 * @return VendorRuntimeStatusProjection Immutable runtime projection with readiness flags per surface.
 */
```

### Surface status local variable

Use this runtime shape in a local PHPDoc when needed:

```php
/** @var array{ownership:bool, profile:bool, finance:bool, statementDelivery:bool, externalIntegration:bool} $surfaceStatus */
```

## 3. `src/Service/Ops/VendorReleaseBaselineReaderService.php`

### Method intent

```php
/**
 * Build a release-baseline projection from aggregated runtime status and artifact presence.
 *
 * Date bounds are forwarded unchanged to the runtime status builder when present.
 * The returned baseline is read-side only and does not alter files, transports, or domain state.
 *
 * @param string      $tenantId Canonical tenant scope.
 * @param string      $vendorId Vendor identifier exposed by runtime surfaces.
 * @param string|null $from     Optional inclusive period start for nested finance and statement reads.
 * @param string|null $to       Optional inclusive period end for nested finance and statement reads.
 * @param string      $currency Canonical currency code for finance-facing sections.
 *
 * @return VendorReleaseBaselineProjection Immutable baseline containing runtime payload, profile summary,
 *                                   artifact presence, issue list, and overall release status.
 */
```

### Profile summary helper

```php
/**
 * Normalize profile readiness fields into a stable summary shape.
 *
 * @param array<string,mixed>|null $profile Raw profile projection as returned by runtime status.
 *
 * @return array{
 *   available: bool,
 *   completionPercent: ?int,
 *   readyForPublishing: ?bool,
 *   nextAction: ?string
 * }
 */
```

### Artifact and issues local variables

```php
/** @var array<string,bool> $artifactStatus */
/** @var list<string> $issues */
```

## 4. `src/Service/VendorFinanceRuntimeProjectionBuilderService.php`

### Class intent

```php
/**
 * Read-side builder for the vendor finance runtime projection.
 *
 * Aggregates ownership context, metric overview, payout-account state, and optional
 * statement data without mutating payouts, balances, or statement artifacts.
 */
```

### Method intent

```php
/**
 * Build a finance-facing runtime projection for one tenant/vendor scope.
 *
 * Date bounds are inclusive when both are provided and are forwarded to metric and statement
 * readers without reinterpretation. Statement data is omitted when the period is incomplete.
 *
 * @param string      $tenantId Canonical tenant scope.
 * @param string      $vendorId Vendor identifier exposed by runtime and API surfaces.
 * @param string|null $from     Optional inclusive period start.
 * @param string|null $to       Optional inclusive period end.
 * @param string      $currency Canonical currency code for metric and statement sections.
 *
 * @return VendorFinanceRuntimeProjection Immutable runtime projection for finance inspection.
 */
```

### Payout account local variable

```php
/**
 * @var array{
 *   provider:mixed,
 *   accountRef:mixed,
 *   currency:mixed,
 *   active:mixed,
 *   createdAt:mixed
 * }|null $payoutAccount
 */
```

## 5. `src/Service/Statement/VendorStatementDeliveryRuntimeProjectionBuilderService.php`

### Class intent

```php
/**
 * Read-side builder for vendor statement-delivery runtime inspection.
 *
 * Combines ownership context, statement data, optional export metadata, and filtered recipient
 * targets without sending email or mutating delivery state.
 */
```

### Method intent

```php
/**
 * Build a statement-delivery runtime projection for one tenant/vendor period.
 *
 * The period bounds are inclusive and are forwarded unchanged to statement and recipient readers.
 * Export metadata is included only when requested through the includeExport flag.
 *
 * @param string $tenantId       Canonical tenant scope.
 * @param string $vendorId       Vendor identifier exposed by runtime and API surfaces.
 * @param string $from           Inclusive period start.
 * @param string $to             Inclusive period end.
 * @param string $currency       Canonical statement currency code.
 * @param bool   $includeExport  When true, generate and inspect a PDF export path.
 *
 * @return VendorStatementDeliveryRuntimeProjection Immutable runtime projection for statement delivery.
 */
```

### Export and recipients local variables

```php
/** @var array{path:string, exists:bool, readable:bool}|null $export */
/** @var list<array{tenantId:string, vendorId:string, email:string, currency:string}> $recipients */
```

## 6. `src/Service/Integration/VendorExternalIntegrationRuntimeProjectionBuilderService.php`

### Method intent

```php
/**
 * Build a runtime projection for neighboring external integration seams.
 *
 * The returned projection reports local readiness and exposed surfaces for CRM registration,
 * webhook consumption, and payout-transfer bridges without issuing live external requests.
 *
 * @param string $tenantId Canonical tenant scope.
 * @param string $vendorId Vendor identifier exposed by runtime and API surfaces.
 *
 * @return VendorExternalIntegrationRuntimeProjection Immutable projection describing local integration readiness.
 */
```

### Local variable shapes

```php
/** @var array<string,mixed>|null $ownership */
/** @var array{serviceClass:string, registerMode:string, runtimeReadable:bool, providerConfigured:bool} $crm */
/** @var array{consumerClass:string, consumerReady:bool, mode:string} $webhooks */
/** @var array{bridgeClass:string, transferMode:string, runtimeReadable:bool} $payoutBridge */
/** @var list<string> $surfaces */
```

## Implementation rule

Apply these PHPDoc blocks directly in code files on the PHPDoc branch.
Do not broaden the wave into unrelated behavioral refactoring.
