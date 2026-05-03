# Vendoring test residual canon sync patch manifest

## Added / renamed files

- `tests/Unit/VendorOwnershipProjectionBuilderTest.php`
- `tests/Unit/Integration/VendorExternalIntegrationRuntimeProjectionBuilderTest.php`
- `tests/Unit/Observability/VendorObservabilityRecordExporterServiceTest.php`
- `tests/Unit/Ops/VendorRuntimeStatusProjectionBuilderTest.php`
- `tests/Unit/Service/VendorTransactionLifecycleServiceTest.php`
- `tests/Unit/Service/VendorCoreServiceTest.php`
- `tests/Unit/Service/VendorFinanceRuntimeProjectionBuilderTest.php`
- `tests/Unit/Service/VendorSecurityStateProjectionBuilderTest.php`
- `tests/Unit/Service/VendorOwnershipProjectionBuilderTest.php`
- `tests/Unit/Service/VendorProfileProjectionBuilderTest.php`
- `tests/Unit/Statement/StatementExporterPdfTest.php`
- `tests/Unit/Statement/VendorStatementDeliveryRuntimeProjectionBuilderTest.php`
- `tests/Unit/Transaction/VendorTransactionLifecycleServiceTest.php`
- `tests/Unit/Form/Ops/VendorTransactionStatusUpdateFormTest.php`
- `tests/Unit/Form/Ops/VendorTransactionCreateFormTest.php`
- `tests/Support/Statement/FakeStatementExporterPdf.php`
- `tests/Support/Transaction/FakeVendorTransactionLifecycle.php`

## Modified files

- `tools/vendoring-service-naming-audit.php`
- `tests/bin/interface-alias-smoke.php`
- `tests/bin/observability-backend-contract-smoke.php`
- `tests/bin/outbound-fault-tolerance-contract-smoke.php`
- `tests/bin/runtime-activation-smoke.php`
- `tests/bin/transaction-error-surface-smoke.php`
- `tests/bin/transaction-idempotency-smoke.php`
- `tests/Unit/Command/SendVendorStatementsCommandTest.php`
- `tests/Unit/Command/VendorRuntimeStatusCommandTest.php`
- `tests/Unit/Controller/VendorProfileControllerTest.php`
- `tests/Unit/Controller/VendorTransactionControllerTest.php`
- `tests/Unit/Infrastructure/InterfaceAliasCoverageTest.php`
- `tests/Unit/Infrastructure/ServiceWiringContractTest.php`
- `tests/Unit/Observability/VendorRuntimeLoggerServiceTest.php`
- `tests/Unit/Observability/VendorRuntimeMetricCollectorServiceTest.php`
- `tests/Unit/Ops/VendorReleaseBaselineReaderTest.php`
- `tests/Unit/Reliability/FileOutboundCircuitBreakerTest.php`
- `tests/Unit/Statement/VendorStatementMailerServiceTest.php`
- `tests/Unit/Traffic/FileWriteRateLimiterTest.php`
- `tests/Unit/Controller/Finance/VendorFinanceRuntimeControllerTest.php`
- `tests/Unit/Controller/Integration/VendorExternalIntegrationRuntimeControllerTest.php`
- `tests/Unit/Controller/Statement/VendorStatementDeliveryRuntimeControllerTest.php`
- `tests/Unit/Controller/Statement/VendorStatementExportControllerTest.php`
- `tests/Integration/Ops/VendorReleaseBaselineRuntimeConsistencyTest.php`
- `tests/Integration/Runtime/VendorRuntimeFinanceConsistencyTest.php`
- `tests/Integration/Runtime/VendorRuntimeProfileReadinessConsistencyTest.php`
- `tests/Integration/Statement/VendorStatementDeliveryRecipientConsistencyTest.php`
- `tests/Integration/Transaction/VendorTransactionSqliteIntegrationTest.php`
- `docs/internal/VENDORING_TEST_RESIDUAL_CANON_SYNC_AUDIT.md`
- `docs/internal/VENDORING_TEST_RESIDUAL_CANON_SYNC_PATCH_MANIFEST.md`

## Deleted obsolete files

These files are deleted by the apply script with backup under `.patch-backup/`:

- `tests/Unit/VendorOwnershipViewBuilderTest.php`
- `tests/Unit/Integration/VendorExternalIntegrationRuntimeViewBuilderTest.php`
- `tests/Unit/Observability/VendorFileObservabilityRecordExporterServiceTest.php`
- `tests/Unit/Ops/VendorRuntimeStatusViewBuilderTest.php`
- `tests/Unit/Security/VendorSecurityServiceTest.php`
- `tests/Unit/Service/VendorFinanceRuntimeViewBuilderTest.php`
- `tests/Unit/Service/VendorOwnershipViewBuilderTest.php`
- `tests/Unit/Service/VendorProfileViewBuilderTest.php`
- `tests/Unit/Service/VendorSecurityServiceTest.php`
- `tests/Unit/Service/VendorSecurityStateViewBuilderTest.php`
- `tests/Unit/Service/VendorServiceTest.php`
- `tests/Unit/Service/VendorTransactionManagerTest.php`
- `tests/Unit/Statement/StatementExporterPDFTest.php`
- `tests/Unit/Statement/VendorStatementDeliveryRuntimeViewBuilderTest.php`
- `tests/Unit/Transaction/VendorTransactionManagerTest.php`
- `tests/Unit/Form/Ops/VendorTransactionCreateTypeTest.php`
- `tests/Unit/Form/Ops/VendorTransactionStatusUpdateTypeTest.php`
- `tests/Support/Statement/FakeStatementExporterPDF.php`
- `tests/Support/Transaction/FakeVendorTransactionManager.php`
