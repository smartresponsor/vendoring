# Vendoring Wave AR Patch Manifest

## Added production files

- `src/Entity/Vendor/VendorCatalogCategoryBannerEntity.php`
- `src/Entity/Vendor/VendorCatalogCategoryChangeRequestEntity.php`
- `src/Entity/Vendor/VendorCatalogCategoryHtmlBlockEntity.php`
- `src/Entity/Vendor/VendorCatalogCategoryPinEntity.php`
- `src/Entity/Vendor/VendorCatalogReviewAssignmentEntity.php`
- `src/Event/Vendor/VendorCategoryDestinationMediaFallbackEvaluatedEvent.php`
- `src/Event/Vendor/VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEvent.php`
- `src/Event/Vendor/VendorCategoryDestinationMediaReadinessEvaluatedEvent.php`
- `src/Event/Vendor/VendorCategorySyndicationFallbackAwarePackageGatedEvent.php`
- `src/Event/Vendor/VendorCategorySyndicationGovernanceTrailRecordedEvent.php`
- `src/Event/Vendor/VendorCategorySyndicationPolicyAwarePackageGatedEvent.php`
- `src/Event/Vendor/VendorCategorySyndicationPublishPackageBuiltEvent.php`
- `src/EventInterface/Vendor/VendorCategoryDestinationMediaFallbackEvaluatedEventInterface.php`
- `src/EventInterface/Vendor/VendorCategoryDestinationMediaPolicyPreferenceEvaluatedEventInterface.php`
- `src/EventInterface/Vendor/VendorCategoryDestinationMediaReadinessEvaluatedEventInterface.php`
- `src/EventInterface/Vendor/VendorCategorySyndicationFallbackAwarePackageGatedEventInterface.php`
- `src/EventInterface/Vendor/VendorCategorySyndicationGovernanceTrailRecordedEventInterface.php`
- `src/EventInterface/Vendor/VendorCategorySyndicationPolicyAwarePackageGatedEventInterface.php`
- `src/EventInterface/Vendor/VendorCategorySyndicationPublishPackageBuiltEventInterface.php`

## Updated active config/smoke/test files

- `ops/policy/config/services_interface.yaml`
- `tests/bin/app-namespace-repository-smoke.php`
- `tests/bin/di-smoke.php`
- `tests/bin/interface-alias-smoke.php`
- `tests/Unit/DependencyInjection/VendoringConfigurationTest.php`
- `tests/Unit/Infrastructure/VendoringExtensionConfigurationContractTest.php`
- `tests/Unit/Observability/VendorMetricCollectorServiceTest.php`
- `tests/Unit/Repository/DoctrineRepositoryContractTest.php`

## Retired touched file

- `tests/Unit/Observability/VendorChainMetricCollectorServiceTest.php`
