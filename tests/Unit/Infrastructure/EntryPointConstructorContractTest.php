<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use App\Vendoring\Command\VendorApiKeyCreateCommand;
use App\Vendoring\Command\VendorApiKeyListCommand;
use App\Vendoring\Command\VendorApiKeyRotateCommand;
use App\Vendoring\Command\VendorSendVendorStatementsCommand;
use App\Vendoring\Service\Http\Vendor\Metric\VendorMetricService;
use App\Vendoring\Service\Http\Vendor\Payout\Account\VendorPayoutAccountService;
use App\Vendoring\Service\Http\Vendor\Payout\VendorPayoutHttpService;
use App\Vendoring\Service\Http\Vendor\Statement\Export\VendorStatementExportService;
use App\Vendoring\Service\Http\Vendor\Statement\VendorStatementHttpService;
use App\Vendoring\Service\Http\Vendor\Summary\VendorSummaryHttpService;
use App\Vendoring\Service\Http\Vendor\Transaction\Operator\VendorTransactionOperatorService;
use App\Vendoring\Service\Http\Vendor\Transaction\VendorTransactionHttpService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EntryPointConstructorContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function entryPointProvider(): iterable
    {
        yield 'send_vendor_statements' => [VendorSendVendorStatementsCommand::class];
        yield 'vendor_api_key_create' => [VendorApiKeyCreateCommand::class];
        yield 'vendor_api_key_list' => [VendorApiKeyListCommand::class];
        yield 'vendor_api_key_rotate' => [VendorApiKeyRotateCommand::class];
        yield 'vendor_summary_http_service' => [VendorSummaryHttpService::class];
        yield 'vendor_metric_http_service' => [VendorMetricService::class];
        yield 'payout_account_http_service' => [VendorPayoutAccountService::class];
        yield 'payout_http_service' => [VendorPayoutHttpService::class];
        yield 'vendor_statement_http_service' => [VendorStatementHttpService::class];
        yield 'vendor_statement_export_http_service' => [VendorStatementExportService::class];
        yield 'vendor_transaction_http_service' => [VendorTransactionHttpService::class];
        yield 'vendor_transaction_operator_http_service' => [VendorTransactionOperatorService::class];
    }

    #[DataProvider('entryPointProvider')]
    public function testEntryPointsUseInterfacesOrApprovedFrameworkContracts(string $className): void
    {
        self::assertTrue(class_exists($className) || interface_exists($className));
        /** @var class-string $className */
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        self::assertNotNull($constructor, sprintf('%s must declare constructor explicitly.', $className));

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            self::assertInstanceOf(\ReflectionNamedType::class, $type, sprintf('%s::%s must have named type.', $className, $parameter->getName()));

            /** @var \ReflectionNamedType $type */
            $typeName = $type->getName();
            $isApprovedFrameworkContract = in_array($typeName, [
                'Doctrine\\ORM\\EntityManagerInterface',
                'Doctrine\\Persistence\\ManagerRegistry',
                'Psr\\Log\\LoggerInterface',
                'Symfony\\Component\\Form\\FormFactoryInterface',
                'Symfony\\Component\\HttpFoundation\\RequestStack',
                'Symfony\\Component\\Mailer\\MailerInterface',
                'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface',
                'Twig\\Environment',
            ], true);

            self::assertTrue(
                str_contains($typeName, 'Interface') || $isApprovedFrameworkContract,
                sprintf('%s::$%s should depend on interfaces/canonical contracts, got %s.', $className, $parameter->getName(), $typeName),
            );
        }
    }
}
