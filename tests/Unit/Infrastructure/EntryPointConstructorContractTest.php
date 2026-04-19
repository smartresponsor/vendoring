<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use App\Vendoring\Command\SendVendorStatementsCommand;
use App\Vendoring\Command\VendorApiKeyCreateCommand;
use App\Vendoring\Command\VendorApiKeyListCommand;
use App\Vendoring\Command\VendorApiKeyRotateCommand;
use App\Vendoring\Controller\Ledger\VendorSummaryController;
use App\Vendoring\Controller\Metric\VendorMetricController;
use App\Vendoring\Controller\Payout\PayoutAccountController;
use App\Vendoring\Controller\Payout\PayoutController;
use App\Vendoring\Controller\Payout\VendorStatementController;
use App\Vendoring\Controller\Statement\VendorStatementExportController;
use App\Vendoring\Controller\VendorTransactionController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EntryPointConstructorContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function entryPointProvider(): iterable
    {
        yield 'send_vendor_statements' => [SendVendorStatementsCommand::class];
        yield 'vendor_api_key_create' => [VendorApiKeyCreateCommand::class];
        yield 'vendor_api_key_list' => [VendorApiKeyListCommand::class];
        yield 'vendor_api_key_rotate' => [VendorApiKeyRotateCommand::class];
        yield 'vendor_summary_controller' => [VendorSummaryController::class];
        yield 'vendor_metric_controller' => [VendorMetricController::class];
        yield 'payout_account_controller' => [PayoutAccountController::class];
        yield 'payout_controller' => [PayoutController::class];
        yield 'vendor_statement_controller' => [VendorStatementController::class];
        yield 'vendor_statement_export_controller' => [VendorStatementExportController::class];
        yield 'vendor_transaction_controller' => [VendorTransactionController::class];
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
                'Symfony\\Component\\Mailer\\MailerInterface',
                'Symfony\\Component\\HttpFoundation\\RequestStack',
                'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface',
            ], true);

            self::assertTrue(
                str_contains($typeName, 'Interface') || $isApprovedFrameworkContract,
                sprintf('%s::$%s should depend on interfaces/canonical contracts, got %s.', $className, $parameter->getName(), $typeName),
            );
        }
    }
}
