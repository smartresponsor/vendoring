# Vendoring PHPDoc Wave A Canon

## Purpose

This document defines the PHPDoc standard for the first documentation-hardening wave focused on runtime and release-read models.

The goal is to make the codebase readable in a deterministic way for:
- phpDocumentor
- human maintainers
- automated tooling
- AI agents

## Scope

Wave A covers runtime and release-facing read models:
- `App\Vendoring\Service\Ops\VendorRuntimeStatusViewBuilder`
- `App\Vendoring\Service\Ops\VendorReleaseBaselineReader`
- `App\Vendoring\Service\VendorFinanceRuntimeViewBuilder`
- `App\Vendoring\Service\Statement\VendorStatementDeliveryRuntimeViewBuilder`
- `App\Vendoring\Service\Integration\VendorExternalIntegrationRuntimeViewBuilder`
- matching `ServiceInterface` contracts for the same surfaces

## Required class-level PHPDoc

Each public class in this wave must declare:
- whether it is read-side or write-side
- whether it has side effects
- what kind of projection or result it assembles
- which adjacent surfaces it aggregates

### Example pattern

```php
/**
 * Read-side aggregator for vendor runtime surfaces.
 *
 * Builds a release-facing projection by combining ownership, profile,
 * finance, statement, and integration views without mutating domain state.
 */
```

## Required method-level PHPDoc

Each public method must document:
- semantic meaning of each parameter
- whether date bounds are inclusive
- currency expectations
- output type meaning
- side-effect expectations

### Example pattern

```php
/**
 * Build a runtime status projection for one tenant/vendor scope.
 *
 * @param string      $tenantId Canonical tenant scope.
 * @param string      $vendorId Vendor identifier as exposed by runtime surfaces.
 * @param string|null $from     Optional inclusive period start.
 * @param string|null $to       Optional inclusive period end.
 * @param string      $currency Canonical currency code for finance sections.
 *
 * @return VendorRuntimeStatusView Immutable runtime projection.
 */
```

## Required array-shape PHPDoc

Whenever a method returns an array or normalizes mixed projection data, use explicit array shapes.

### Example pattern

```php
/**
 * @param array<string,mixed>|null $profile
 *
 * @return array{
 *   available: bool,
 *   completionPercent: ?int,
 *   readyForPublishing: ?bool,
 *   nextAction: ?string
 * }
 */
```

## Rules

- prefer domain meaning over generic wording
- never describe a read model as if it performs writes
- explicitly say when a method is side-effect free
- explicitly mark optional fields as nullable
- document list payloads as `list<T>` when order matters
- document associative runtime maps as `array<string,mixed>` only when a stricter shape is not practical
- keep Symfony-oriented naming and boundaries intact

## Expected outcome

After Wave A, runtime and release surfaces should be:
- phpDocumentor-ready
- machine-readable
- unambiguous for maintainers
- suitable as a base for later docs generation and release evidence
