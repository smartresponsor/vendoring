# Vendoring mirror-contract canonization patch manifest

## Added / renamed files

- `src/Service/Reliability/VendorOutboundCircuitBreakerService.php`
- `src/Service/Traffic/VendorWriteRateLimiterService.php`
- `src/Service/Observability/VendorObservabilityRecordExporterService.php`
- `src/Service/Observability/VendorMetricCollectorService.php`
- `docs/internal/VENDORING_MIRROR_CONTRACT_CANONIZATION_AUDIT.md`
- `docs/internal/VENDORING_MIRROR_CONTRACT_CANONIZATION_PATCH_MANIFEST.md`

## Updated files

- `config/component/services.yaml`

## Removed old files

- `src/Service/Reliability/VendorFileOutboundCircuitBreakerService.php`
- `src/Service/Traffic/VendorFileWriteRateLimiterService.php`
- `src/Service/Observability/VendorFileObservabilityRecordExporterService.php`
- `src/Service/Observability/VendorChainMetricCollectorService.php`

## Apply policy

Apply as touched-file overlay only. Do not delete or replace the whole repository. The removed files above are the only source files intentionally deleted in this pass.
