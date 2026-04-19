<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use App\Vendoring\Repository\Ledger\LedgerEntryRepository;
use App\Vendoring\Repository\Payout\PayoutAccountRepository;
use App\Vendoring\Repository\Payout\PayoutRepository;
use App\Vendoring\Repository\VendorApiKeyRepository;
use App\Vendoring\Repository\VendorBillingRepository;
use App\Vendoring\Repository\VendorMediaRepository;
use App\Vendoring\Repository\VendorPassportRepository;
use App\Vendoring\Repository\VendorProfileRepository;
use App\Vendoring\Repository\VendorRepository;
use App\Vendoring\Observability\Service\FileObservabilityRecordExporter;
use App\Vendoring\Service\Metric\VendorMetricService;
use App\Vendoring\Service\Ops\ReleaseManifestBuilder;
use App\Vendoring\Service\Ops\RollbackDecisionEvaluator;
use App\Vendoring\Service\Security\VendorAccessResolver;
use App\Vendoring\Service\Security\VendorAuthorizationMatrix;
use App\Vendoring\Service\Rollout\CanaryRolloutCoordinator;
use App\Vendoring\Service\Rollout\FeatureFlagService;
use App\Vendoring\Service\Policy\OutboundOperationPolicy;
use App\Vendoring\Service\Reliability\FileOutboundCircuitBreaker;
use App\Vendoring\Service\Rollout\TrafficCohortResolver;
use App\Vendoring\Service\Payout\VendorPayoutService;
use App\Vendoring\Service\Statement\StatementExporterPDF;
use App\Vendoring\Service\Statement\VendorStatementMailerService;
use App\Vendoring\Service\Statement\VendorStatementRecipientProvider;
use App\Vendoring\Service\Statement\VendorStatementService;
use App\Vendoring\Service\VendorSecurityService;
use App\Vendoring\Service\VendorTransactionManager;
use App\Vendoring\Service\WebhooksConsumer\VendorWebhooksConsumerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServiceWiringContractTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function serviceAliasMapProvider(): iterable
    {
        yield 'vendor_api_key_repository' => ['App\Vendoring\\RepositoryInterface\\VendorApiKeyRepositoryInterface', VendorApiKeyRepository::class];
        yield 'vendor_repository' => ['App\Vendoring\\RepositoryInterface\\VendorRepositoryInterface', VendorRepository::class];
        yield 'vendor_profile_repository' => ['App\Vendoring\\RepositoryInterface\\VendorProfileRepositoryInterface', VendorProfileRepository::class];
        yield 'vendor_billing_repository' => ['App\Vendoring\\RepositoryInterface\\VendorBillingRepositoryInterface', VendorBillingRepository::class];
        yield 'vendor_media_repository' => ['App\Vendoring\\RepositoryInterface\\VendorMediaRepositoryInterface', VendorMediaRepository::class];
        yield 'vendor_passport_repository' => ['App\Vendoring\\RepositoryInterface\\VendorPassportRepositoryInterface', VendorPassportRepository::class];
        yield 'ledger_entry_repository' => ['App\Vendoring\\RepositoryInterface\\Ledger\\LedgerEntryRepositoryInterface', LedgerEntryRepository::class];
        yield 'payout_repository' => ['App\Vendoring\\RepositoryInterface\\Payout\\PayoutRepositoryInterface', PayoutRepository::class];
        yield 'payout_account_repository' => ['App\Vendoring\\RepositoryInterface\\Payout\\PayoutAccountRepositoryInterface', PayoutAccountRepository::class];
        yield 'vendor_security_service' => ['App\Vendoring\\ServiceInterface\\VendorSecurityServiceInterface', VendorSecurityService::class];
        yield 'outbound_operation_policy' => ['App\Vendoring\ServiceInterface\Policy\OutboundOperationPolicyInterface', OutboundOperationPolicy::class];
        yield 'outbound_circuit_breaker' => ['App\Vendoring\ServiceInterface\Reliability\OutboundCircuitBreakerInterface', FileOutboundCircuitBreaker::class];
        yield 'observability_record_exporter' => ['App\Vendoring\ServiceInterface\Observability\ObservabilityRecordExporterInterface', FileObservabilityRecordExporter::class];
        yield 'release_manifest_builder' => ['App\Vendoring\ServiceInterface\Ops\ReleaseManifestBuilderInterface', ReleaseManifestBuilder::class];
        yield 'rollback_decision_evaluator' => ['App\Vendoring\ServiceInterface\Ops\RollbackDecisionEvaluatorInterface', RollbackDecisionEvaluator::class];
        yield 'canary_rollout_coordinator' => ['App\Vendoring\ServiceInterface\Rollout\CanaryRolloutCoordinatorInterface', CanaryRolloutCoordinator::class];
        yield 'vendor_transaction_manager' => ['App\Vendoring\\ServiceInterface\\VendorTransactionManagerInterface', VendorTransactionManager::class];
        yield 'vendor_metric_service' => ['App\Vendoring\\ServiceInterface\\Metric\\VendorMetricServiceInterface', VendorMetricService::class];
        yield 'payout_service' => ['App\Vendoring\\ServiceInterface\\Payout\\VendorPayoutServiceInterface', VendorPayoutService::class];
        yield 'statement_service' => ['App\Vendoring\\ServiceInterface\\Statement\\VendorStatementServiceInterface', VendorStatementService::class];
        yield 'statement_exporter' => ['App\Vendoring\\ServiceInterface\\Statement\\StatementExporterPDFInterface', StatementExporterPDF::class];
        yield 'statement_mailer' => ['App\Vendoring\\ServiceInterface\\Statement\\VendorStatementMailerServiceInterface', VendorStatementMailerService::class];
        yield 'statement_recipient_provider' => ['App\Vendoring\\ServiceInterface\\Statement\\VendorStatementRecipientProviderInterface', VendorStatementRecipientProvider::class];
        yield 'webhooks_consumer' => ['App\Vendoring\\ServiceInterface\\WebhooksConsumer\\VendorWebhooksConsumerServiceInterface', VendorWebhooksConsumerService::class];
    }

    #[DataProvider('serviceAliasMapProvider')]
    public function testServicesYamlDefinesCanonicalAliases(string $interfaceClass, string $implementationClass): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3) . '/config/vendor_services.yaml');

        self::assertStringContainsString($interfaceClass . ':', $services);
        self::assertStringContainsString("'@" . $implementationClass . "'", $services);
    }

    public function testServicesYamlExcludesNonServiceTreesFromAppResource(): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3) . '/config/vendor_services.yaml');

        foreach ([
            '../src/DTO/',
            '../src/EntityInterface/',
            '../src/Event/',
            '../src/RepositoryInterface/',
            '../src/ServiceInterface/',
            '../src/ValueObject/',
        ] as $excludedPath) {
            self::assertStringContainsString($excludedPath, $services);
        }
    }

    public function testVendorApiKeyRepositoryClassExistsAsConcreteDoctrineRepository(): void
    {
        $repositoryPath = dirname(__DIR__, 3) . '/src/Repository/VendorApiKeyRepository.php';
        self::assertFileExists($repositoryPath);

        $contents = (string) file_get_contents($repositoryPath);
        self::assertStringContainsString('final class VendorApiKeyRepository', $contents);
        self::assertStringContainsString('implements VendorApiKeyRepositoryInterface', $contents);
    }
}
