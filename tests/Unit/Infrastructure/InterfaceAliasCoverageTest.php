<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class InterfaceAliasCoverageTest extends TestCase
{
    public function testServicesConfigurationCoversCanonicalRepositoryAndServiceInterfaces(): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3) . '/config/vendor_services.yaml');
        $servicesVendorTransactions = (string) file_get_contents(dirname(__DIR__, 3) . '/config/vendor_services_transactions.yaml');
        $config = $services . "\n" . $servicesVendorTransactions;

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
            'App\Vendoring\\RepositoryInterface\\Ledger\\LedgerEntryRepositoryInterface' => 'App\Vendoring\\Repository\\Ledger\\LedgerEntryRepository',
            'App\Vendoring\\RepositoryInterface\\Payout\\PayoutAccountRepositoryInterface' => 'App\Vendoring\\Repository\\Payout\\PayoutAccountRepository',
            'App\Vendoring\\RepositoryInterface\\Payout\\PayoutRepositoryInterface' => 'App\Vendoring\\Repository\\Payout\\PayoutRepository',
            'App\Vendoring\\ServiceInterface\\VendorCrmServiceInterface' => 'App\Vendoring\\Service\\VendorCrmService',
            'App\Vendoring\\ServiceInterface\\VendorBillingServiceInterface' => 'App\Vendoring\\Service\\VendorBillingService',
            'App\Vendoring\\ServiceInterface\\VendorDocumentServiceInterface' => 'App\Vendoring\\Service\\VendorDocumentService',
            'App\Vendoring\\ServiceInterface\\VendorMediaServiceInterface' => 'App\Vendoring\\Service\\VendorMediaService',
            'App\Vendoring\\ServiceInterface\\VendorPassportServiceInterface' => 'App\Vendoring\\Service\\VendorPassportService',
            'App\Vendoring\\ServiceInterface\\VendorProfileServiceInterface' => 'App\Vendoring\\Service\\VendorProfileService',
            'App\Vendoring\\ServiceInterface\\VendorSecurityServiceInterface' => 'App\Vendoring\\Service\\VendorSecurityService',
            'App\Vendoring\\ServiceInterface\\VendorServiceInterface' => 'App\Vendoring\\Service\\VendorService',
            'App\Vendoring\\ServiceInterface\\VendorTransactionManagerInterface' => 'App\Vendoring\\Service\\VendorTransactionManager',
            'App\Vendoring\\ServiceInterface\\WebhooksConsumer\\VendorWebhooksConsumerServiceInterface' => 'App\Vendoring\\Service\\WebhooksConsumer\\VendorWebhooksConsumerService',
            'App\Vendoring\\ServiceInterface\\Ledger\\VendorDoubleEntryServiceInterface' => 'App\Vendoring\\Service\\Ledger\\VendorDoubleEntryService',
            'App\Vendoring\\ServiceInterface\\Metric\\VendorMetricServiceInterface' => 'App\Vendoring\\Service\\Metric\\VendorMetricService',
            'App\Vendoring\\ServiceInterface\\Payout\\VendorPayoutProviderServiceInterface' => 'App\Vendoring\\Service\\Payout\\VendorPayoutProviderService',
            'App\Vendoring\\ServiceInterface\\Payout\\VendorPayoutServiceInterface' => 'App\Vendoring\\Service\\Payout\\VendorPayoutService',
            'App\Vendoring\\ServiceInterface\\Payout\\VendorSettlementCalculatorServiceInterface' => 'App\Vendoring\\Service\\Payout\\VendorSettlementCalculatorService',
            'App\Vendoring\\ServiceInterface\\Statement\\StatementExporterPDFInterface' => 'App\Vendoring\\Service\\Statement\\StatementExporterPDF',
            'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementMailerServiceInterface' => 'App\Vendoring\\Service\\Statement\\VendorStatementMailerService',
            'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementRecipientProviderInterface' => 'App\Vendoring\\Service\\Statement\\VendorStatementRecipientProvider',
            'App\Vendoring\\ServiceInterface\\Statement\\VendorStatementServiceInterface' => 'App\Vendoring\\Service\\Statement\\VendorStatementService',
            'App\Vendoring\\ServiceInterface\\Api\\TenantQueryRequestResolverInterface' => 'App\Vendoring\\Service\\Api\\TenantQueryRequestResolver',
            'App\Vendoring\\ServiceInterface\\Api\\StatementWindowQueryRequestResolverInterface' => 'App\Vendoring\\Service\\Api\\StatementWindowQueryRequestResolver',
            'App\Vendoring\\ServiceInterface\\VendorProfileRequestResolverInterface' => 'App\Vendoring\\Service\\VendorProfileRequestResolver',
            'App\Vendoring\ServiceInterface\Rollout\FeatureFlagServiceInterface' => 'App\Vendoring\Service\Rollout\FeatureFlagService',
            'App\Vendoring\ServiceInterface\Rollout\CanaryRolloutCoordinatorInterface' => 'App\Vendoring\Service\Rollout\CanaryRolloutCoordinator',
        ];
    }
}
