<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class InterfaceAliasCoverageTest extends TestCase
{
    public function testServicesConfigurationCoversCanonicalRepositoryAndServiceInterfaces(): void
    {
        $config = (string) file_get_contents(dirname(__DIR__, 3) . '/config/component/services.yaml');

        foreach ($this->expectedAliasMap() as $interfaceClass => $implementationClass) {
            self::assertStringContainsString($interfaceClass . ':', $config, 'Missing alias for ' . $interfaceClass);
            self::assertStringContainsString("'@" . $implementationClass . "'", $config, 'Missing target for ' . $interfaceClass);
            self::assertTrue(class_exists($implementationClass), 'Missing implementation class ' . $implementationClass);
        }
    }

    /**
     * @return array<string, string>
     */
    private function expectedAliasMap(): array
    {
        return [
            'App\Vendoring\\RepositoryInterface\\VendorAnalyticsRepositoryInterface' => 'App\Vendoring\\Repository\\VendorAnalyticsRepository',
            'App\Vendoring\\RepositoryInterface\\VendorApiKeyRepositoryInterface' => 'App\Vendoring\\Repository\\VendorApiKeyRepository',
            'App\Vendoring\\RepositoryInterface\\VendorAttachmentRepositoryInterface' => 'App\Vendoring\\Repository\\VendorAttachmentRepository',
            'App\Vendoring\\RepositoryInterface\\VendorBillingRepositoryInterface' => 'App\Vendoring\\Repository\\VendorBillingRepository',
            'App\Vendoring\\RepositoryInterface\\VendorDocumentRepositoryInterface' => 'App\Vendoring\\Repository\\VendorDocumentRepository',
            'App\Vendoring\\RepositoryInterface\\VendorLedgerBindingRepositoryInterface' => 'App\Vendoring\\Repository\\VendorLedgerBindingRepository',
            'App\Vendoring\\RepositoryInterface\\VendorMediaRepositoryInterface' => 'App\Vendoring\\Repository\\VendorMediaRepository',
            'App\Vendoring\\RepositoryInterface\\VendorPassportRepositoryInterface' => 'App\Vendoring\\Repository\\VendorPassportRepository',
            'App\Vendoring\\RepositoryInterface\\VendorProfileRepositoryInterface' => 'App\Vendoring\\Repository\\VendorProfileRepository',
            'App\Vendoring\\RepositoryInterface\\VendorRepositoryInterface' => 'App\Vendoring\\Repository\\VendorRepository',
            'App\Vendoring\\RepositoryInterface\\VendorSecurityRepositoryInterface' => 'App\Vendoring\\Repository\\VendorSecurityRepository',
            'App\Vendoring\\RepositoryInterface\\VendorTransactionRepositoryInterface' => 'App\Vendoring\\Repository\\VendorTransactionRepository',
            'App\Vendoring\\RepositoryInterface\\Ledger\\VendorLedgerEntryRepositoryInterface' => 'App\Vendoring\\Repository\\Ledger\\VendorLedgerEntryRepository',
            'App\Vendoring\\RepositoryInterface\\VendorPayoutEntity\\VendorPayoutAccountRepositoryInterface' => 'App\Vendoring\\Repository\\VendorPayoutEntity\\VendorPayoutAccountRepository',
            'App\Vendoring\\RepositoryInterface\\VendorPayoutEntity\\VendorPayoutRepositoryInterface' => 'App\Vendoring\\Repository\\VendorPayoutEntity\\VendorPayoutRepository',
            'App\Vendoring\\ServiceInterface\\VendorCrmServiceInterface' => 'App\Vendoring\\Service\\VendorCrmService',
            'App\Vendoring\\ServiceInterface\\VendorBillingServiceInterface' => 'App\Vendoring\\Service\\VendorBillingService',
            'App\Vendoring\\ServiceInterface\\VendorDocumentServiceInterface' => 'App\Vendoring\\Service\\VendorDocumentService',
            'App\Vendoring\\ServiceInterface\\VendorMediaServiceInterface' => 'App\Vendoring\\Service\\VendorMediaService',
            'App\Vendoring\\ServiceInterface\\VendorPassportServiceInterface' => 'App\Vendoring\\Service\\VendorPassportService',
            'App\Vendoring\\ServiceInterface\\VendorProfileServiceInterface' => 'App\Vendoring\\Service\\VendorProfileService',
            'App\Vendoring\\ServiceInterface\\VendorSecurityServiceInterface' => 'App\Vendoring\\Service\\VendorSecurityService',
            'App\Vendoring\\ServiceInterface\\VendorServiceInterface' => 'App\Vendoring\\Service\\VendorService',
            'App\Vendoring\\ServiceInterface\\VendorTransactionManagerServiceInterface' => 'App\Vendoring\\Service\\VendorTransactionManagerService',
            'App\Vendoring\\ServiceInterface\\WebhooksConsumer\\VendorWebhooksConsumerServiceInterface' => 'App\Vendoring\\Service\\WebhooksConsumer\\VendorWebhooksConsumerService',
            'App\Vendoring\\ServiceInterface\\Ledger\\VendorDoubleEntryServiceInterface' => 'App\Vendoring\\Service\\Ledger\\VendorDoubleEntryService',
            'App\Vendoring\\ServiceInterface\\Metric\\VendorMetricServiceInterface' => 'App\Vendoring\\Service\\Metric\\VendorMetricService',
            'App\Vendoring\\ServiceInterface\\VendorPayoutEntity\\VendorPayoutProviderServiceInterface' => 'App\Vendoring\\Service\\VendorPayoutEntity\\VendorPayoutProviderService',
            'App\Vendoring\\ServiceInterface\\VendorPayoutEntity\\VendorPayoutServiceInterface' => 'App\Vendoring\\Service\\VendorPayoutEntity\\VendorPayoutService',
            'App\Vendoring\\ServiceInterface\\VendorPayoutEntity\\VendorSettlementCalculatorServiceInterface' => 'App\Vendoring\\Service\\VendorPayoutEntity\\VendorSettlementCalculatorService',
            'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementExporterPDFServiceInterface' => 'App\Vendoring\\Service\\Statement\\VendorStatementExporterPDFService',
            'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementMailerServiceInterface' => 'App\Vendoring\\Service\\Statement\\VendorStatementMailerService',
            'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementRecipientProviderServiceInterface' => 'App\Vendoring\\Service\\Statement\\VendorStatementRecipientProviderService',
            'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementServiceInterface' => 'App\Vendoring\\Service\\Statement\\VendorStatementService',
            'App\Vendoring\\ServiceInterface\\Api\\VendorTenantQueryRequestResolverServiceInterface' => 'App\Vendoring\\Service\\Api\\VendorTenantQueryRequestResolverService',
            'App\Vendoring\\ServiceInterface\\Api\\VendorStatementWindowQueryRequestResolverServiceInterface' => 'App\Vendoring\\Service\\Api\\VendorStatementWindowQueryRequestResolverService',
            'App\Vendoring\\ServiceInterface\\VendorProfileRequestResolverServiceInterface' => 'App\Vendoring\\Service\\VendorProfileRequestResolverService',
            'App\Vendoring\ServiceInterface\Rollout\VendorFeatureFlagServiceInterface' => 'App\Vendoring\Service\Rollout\VendorFeatureFlagService',
            'App\Vendoring\ServiceInterface\Rollout\VendorCanaryRolloutCoordinatorServiceInterface' => 'App\Vendoring\Service\Rollout\VendorCanaryRolloutCoordinatorService',
        ];
    }
}
