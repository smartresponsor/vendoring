<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Repository\Ledger\LedgerEntryRepository;
use App\Repository\Payout\PayoutAccountRepository;
use App\Repository\Payout\PayoutRepository;
use App\Repository\VendorApiKeyRepository;
use App\Repository\VendorBillingRepository;
use App\Repository\VendorMediaRepository;
use App\Repository\VendorPassportRepository;
use App\Repository\VendorProfileRepository;
use App\Repository\VendorRepository;
use App\Service\Metric\VendorMetricService;
use App\Service\Payout\PayoutService;
use App\Service\Statement\StatementExporterPDF;
use App\Service\Statement\StatementMailerService;
use App\Service\Statement\VendorStatementRecipientProvider;
use App\Service\Statement\VendorStatementService;
use App\Service\VendorSecurityService;
use App\Service\VendorTransactionManager;
use App\Service\WebhooksConsumer\WebhooksConsumerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServiceWiringContractTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function serviceAliasMapProvider(): iterable
    {
        yield 'vendor_api_key_repository' => ['App\\RepositoryInterface\\VendorApiKeyRepositoryInterface', VendorApiKeyRepository::class];
        yield 'vendor_repository' => ['App\\RepositoryInterface\\VendorRepositoryInterface', VendorRepository::class];
        yield 'vendor_profile_repository' => ['App\\RepositoryInterface\\VendorProfileRepositoryInterface', VendorProfileRepository::class];
        yield 'vendor_billing_repository' => ['App\\RepositoryInterface\\VendorBillingRepositoryInterface', VendorBillingRepository::class];
        yield 'vendor_media_repository' => ['App\\RepositoryInterface\\VendorMediaRepositoryInterface', VendorMediaRepository::class];
        yield 'vendor_passport_repository' => ['App\\RepositoryInterface\\VendorPassportRepositoryInterface', VendorPassportRepository::class];
        yield 'ledger_entry_repository' => ['App\\RepositoryInterface\\Ledger\\LedgerEntryRepositoryInterface', LedgerEntryRepository::class];
        yield 'payout_repository' => ['App\\RepositoryInterface\\Payout\\PayoutRepositoryInterface', PayoutRepository::class];
        yield 'payout_account_repository' => ['App\\RepositoryInterface\\Payout\\PayoutAccountRepositoryInterface', PayoutAccountRepository::class];
        yield 'vendor_security_service' => ['App\\ServiceInterface\\VendorSecurityServiceInterface', VendorSecurityService::class];
        yield 'vendor_transaction_manager' => ['App\\ServiceInterface\\VendorTransactionManagerInterface', VendorTransactionManager::class];
        yield 'vendor_metric_service' => ['App\\ServiceInterface\\Metric\\VendorMetricServiceInterface', VendorMetricService::class];
        yield 'payout_service' => ['App\\ServiceInterface\\Payout\\PayoutServiceInterface', PayoutService::class];
        yield 'statement_service' => ['App\\ServiceInterface\\Statement\\VendorStatementServiceInterface', VendorStatementService::class];
        yield 'statement_exporter' => ['App\\ServiceInterface\\Statement\\StatementExporterPDFInterface', StatementExporterPDF::class];
        yield 'statement_mailer' => ['App\\ServiceInterface\\Statement\\StatementMailerServiceInterface', StatementMailerService::class];
        yield 'statement_recipient_provider' => ['App\\ServiceInterface\\Statement\\VendorStatementRecipientProviderInterface', VendorStatementRecipientProvider::class];
        yield 'webhooks_consumer' => ['App\\ServiceInterface\\WebhooksConsumer\\WebhooksConsumerServiceInterface', WebhooksConsumerService::class];
    }

    #[DataProvider('serviceAliasMapProvider')]
    public function testServicesYamlDefinesCanonicalAliases(string $interfaceClass, string $implementationClass): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3).'/config/services.yaml');

        self::assertStringContainsString($interfaceClass.':', $services);
        self::assertStringContainsString("'@".$implementationClass."'", $services);
    }

    public function testServicesYamlExcludesNonServiceTreesFromAppResource(): void
    {
        $services = (string) file_get_contents(dirname(__DIR__, 3).'/config/services.yaml');

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
        self::assertTrue(class_exists(VendorApiKeyRepository::class));
    }
}
