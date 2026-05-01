# Vendoring mirror-contract canonization audit

## Scope

This pass normalizes service implementation names that were still bound to broader mirrored service contracts through storage/backend-specific class names. The change keeps the existing Symfony-oriented service/interface mirror and does not introduce ports/adapters or a Domain layer.

## Findings

- `VendorFileOutboundCircuitBreakerService` implemented `VendorOutboundCircuitBreakerServiceInterface`, but the class name exposed a backend detail while the contract is the canonical runtime capability.
- `VendorFileWriteRateLimiterService` implemented `VendorWriteRateLimiterServiceInterface`, with the same backend-detail leak.
- `VendorFileObservabilityRecordExporterService` implemented `VendorObservabilityRecordExporterServiceInterface`, while the interface already defines the canonical service surface.
- `VendorChainMetricCollectorService` was the configured primary implementation of `VendorMetricCollectorServiceInterface`; the chain/composite behavior is an implementation detail and should not be the service alias name.

## Changes

- Renamed the primary reliability implementation to `VendorOutboundCircuitBreakerService`.
- Renamed the primary traffic implementation to `VendorWriteRateLimiterService`.
- Renamed the primary observability exporter to `VendorObservabilityRecordExporterService`.
- Renamed the configured primary metric collector to `VendorMetricCollectorService`.
- Updated `config/component/services.yaml` aliases and explicit service definitions.

## Remaining notes

- `VendorMetricEmitterService` and `VendorRuntimeMetricCollectorService` still implement `VendorMetricCollectorServiceInterface` intentionally as concrete downstream collectors.
- This pass does not change persistence behavior; file-backed storage remains in the implementation body.
- A later runtime-hardening pass can externalize storage directories and add alternative backend implementations if needed.
