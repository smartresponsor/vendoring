<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class InterfaceAliasCoverageTest extends TestCase
{
    public function testServicesConfigurationCoversCanonicalRepositoryAndServiceInterfaces(): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3).'/config/services.yaml');
        $servicesVendorTransactions = (string) file_get_contents(dirname(__DIR__, 3).'/config/services_vendor_transactions.yaml');
        $config = $services."\n".$servicesVendorTransactions;

        foreach ($this->expectedAliasMap() as $interfaceClass => $implementationClass) {
            self::assertStringContainsString($interfaceClass.':', $config, 'Missing alias for '.$interfaceClass);
            self::assertStringContainsString("'@".$implementationClass."'", $config, 'Missing target for '.$interfaceClass);
            self::assertTrue(class_exists($implementationClass), 'Missing implementation class '.$implementationClass);
        }
    }

    /**
     * @return array<string, string>
     */
    private function expectedAliasMap(): array
    {
        return [
            'App\\RepositoryInterface\\VendorAnalyticsRepositoryInterface' => 'App\\Repository\\VendorAnalyticsRepository',
            'App\\RepositoryInterface\\VendorApiKeyRepositoryInterface' => 'App\\Repository\\VendorApiKeyRepository',
            'App\\RepositoryInterface\\VendorAttachmentRepositoryInterface' => 'App\\Repository\\VendorAttachmentRepository',
            'App\\RepositoryInterface\\VendorBillingRepositoryInterface' => 'App\\Repository\\VendorBillingRepository',
            'App\\RepositoryInterface\\VendorDocumentRepositoryInterface' => 'App\\Repository\\VendorDocumentRepository',
            'App\\RepositoryInterface\\VendorLedgerBindingRepositoryInterface' => 'App\\Repository\\VendorLedgerBindingRepository',
            'App\\RepositoryInterface\\VendorMediaRepositoryInterface' => 'App\\Repository\\VendorMediaRepository',
            'App\\RepositoryInterface\\VendorPassportRepositoryInterface' => 'App\\Repository\\VendorPassportRepository',
            'App\\RepositoryInterface\\VendorProfileRepositoryInterface' => 'App\\Repository\\VendorProfileRepository',
            'App\\RepositoryInterface\\VendorRepositoryInterface' => 'App\\Repository\\VendorRepository',
            'App\\RepositoryInterface\\VendorSecurityRepositoryInterface' => 'App\\Repository\\VendorSecurityRepository',
            'App\\RepositoryInterface\\VendorTransactionRepositoryInterface' => 'App\\Repository\\VendorTransactionRepository',
            'App\\RepositoryInterface\\Ledger\\LedgerEntryRepositoryInterface' => 'App\\Repository\\Ledger\\LedgerEntryRepository',
            'App\\RepositoryInterface\\Payout\\PayoutAccountRepositoryInterface' => 'App\\Repository\\Payout\\PayoutAccountRepository',
            'App\\RepositoryInterface\\Payout\\PayoutRepositoryInterface' => 'App\\Repository\\Payout\\PayoutRepository',
            'App\\ServiceInterface\\CrmServiceInterface' => 'App\\Service\\CrmService',
            'App\\ServiceInterface\\VendorBillingServiceInterface' => 'App\\Service\\VendorBillingService',
            'App\\ServiceInterface\\VendorDocumentServiceInterface' => 'App\\Service\\VendorDocumentService',
            'App\\ServiceInterface\\VendorMediaServiceInterface' => 'App\\Service\\VendorMediaService',
            'App\\ServiceInterface\\VendorPassportServiceInterface' => 'App\\Service\\VendorPassportService',
            'App\\ServiceInterface\\VendorProfileServiceInterface' => 'App\\Service\\VendorProfileService',
            'App\\ServiceInterface\\VendorSecurityServiceInterface' => 'App\\Service\\VendorSecurityService',
            'App\\ServiceInterface\\VendorServiceInterface' => 'App\\Service\\VendorService',
            'App\\ServiceInterface\\VendorTransactionManagerInterface' => 'App\\Service\\VendorTransactionManager',
            'App\\ServiceInterface\\WebhooksConsumer\\WebhooksConsumerServiceInterface' => 'App\\Service\\WebhooksConsumer\\WebhooksConsumerService',
            'App\\ServiceInterface\\Ledger\\DoubleEntryServiceInterface' => 'App\\Service\\Ledger\\DoubleEntryService',
            'App\\ServiceInterface\\Metric\\VendorMetricServiceInterface' => 'App\\Service\\Metric\\VendorMetricService',
            'App\\ServiceInterface\\Payout\\PayoutProviderBridgeInterface' => 'App\\Service\\Payout\\PayoutProviderBridge',
            'App\\ServiceInterface\\Payout\\PayoutServiceInterface' => 'App\\Service\\Payout\\PayoutService',
            'App\\ServiceInterface\\Payout\\VendorSettlementCalculatorInterface' => 'App\\Service\\Payout\\VendorSettlementCalculator',
            'App\\ServiceInterface\\Statement\\StatementExporterPDFInterface' => 'App\\Service\\Statement\\StatementExporterPDF',
            'App\\ServiceInterface\\Statement\\StatementMailerServiceInterface' => 'App\\Service\\Statement\\StatementMailerService',
            'App\\ServiceInterface\\Statement\\VendorStatementRecipientProviderInterface' => 'App\\Service\\Statement\\VendorStatementRecipientProvider',
            'App\\ServiceInterface\\Statement\\VendorStatementServiceInterface' => 'App\\Service\\Statement\\VendorStatementService',
        ];
    }
}
