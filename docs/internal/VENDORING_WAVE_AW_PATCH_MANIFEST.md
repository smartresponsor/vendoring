# Vendoring Wave AW patch manifest

## Added

- `docs/internal/VENDORING_WAVE_AW_PHPSTAN_81_RESIDUAL_SYNC.md`
- `docs/internal/VENDORING_WAVE_AW_PATCH_MANIFEST.md`

## Modified

- `phpstan.neon`
- `src/Controller/Vendor/VendorOwnershipMutationController.php`
- `src/Entity/Vendor/VendorCatalogCategoryBannerEntity.php`
- `src/Entity/Vendor/VendorCatalogCategoryHtmlBlockEntity.php`
- `src/Service/Ownership/VendorOwnershipProjectionBuilderService.php`
- `src/Service/Ownership/VendorOwnershipWriteService.php`
- `src/Service/Statement/VendorStatementMailerService.php`
- `tests/Integration/Transaction/VendorTransactionSqliteIntegrationTest.php`
- `tests/Support/Runtime/KernelRuntimeHarness.php`
- `tests/Support/Transaction/DoctrineBackedVendorTransactionRepository.php`
- `tests/Support/Transaction/FakeVendorTransactionLifecycle.php`
- `tests/Support/Transaction/InMemoryVendorTransactionRepository.php`
- `tests/Unit/Command/VendorPayoutCreateCommandTest.php`
- `tests/Unit/Controller/VendorTransactionControllerTest.php`
- `tests/Unit/Infrastructure/InterfaceAliasCoverageTest.php`
- `tests/Unit/Observability/VendorRuntimeLoggerServiceTest.php`
- `tests/Unit/Observability/VendorRuntimeMetricCollectorServiceTest.php`
- `tests/Unit/Payout/VendorPayoutServiceTest.php`
- `tests/Unit/Repository/Payout/PayoutAccountRepositoryTest.php`
- `tests/Unit/Repository/Payout/PayoutRepositoryTest.php`
- `tests/Unit/Security/VendorAccessResolverTest.php`
- `tests/Unit/Security/VendorApiKeyServiceAuthorizationHeaderTest.php`
- `tests/Unit/Security/VendorUserAssignmentServiceRoleNormalizationTest.php`
- `tests/Unit/Service/CatalogMerchServiceTest.php`
- `tests/Unit/Service/VendorApiKeyServiceTest.php`
- `tests/Unit/Service/VendorBillingServiceTest.php`
- `tests/Unit/Service/VendorCoreServiceTest.php`
- `tests/Unit/Service/VendorCrmServiceTest.php`
- `tests/Unit/Service/VendorOwnershipProjectionBuilderTest.php`
- `tests/Unit/Service/VendorProfileProjectionBuilderTest.php`
- `tests/Unit/Service/VendorProfileServiceTest.php`
- `tests/Unit/Statement/VendorStatementMailerServiceTest.php`
- `tests/Unit/Statement/VendorStatementRecipientProviderTest.php`
- `tests/Unit/Transaction/VendorTransactionLifecycleServiceTest.php`
- `tests/Unit/VendorOwnershipProjectionBuilderTest.php`
- `tests/bin/fresh-db-boot-smoke.php`
- `tests/bin/observability-backend-contract-smoke.php`
- `tests/bin/outbound-fault-tolerance-contract-smoke.php`
- `tests/bin/ownership-runtime-synthetic-probe.php`

## Removed

None.
