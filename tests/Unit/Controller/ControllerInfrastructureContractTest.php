<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Controller;

use App\Vendoring\Controller\Ledger\VendorSummaryController;
use App\Vendoring\Controller\Metric\VendorMetricController;
use App\Vendoring\Controller\Payout\PayoutAccountController;
use App\Vendoring\Controller\Payout\PayoutController;
use App\Vendoring\Controller\Payout\VendorStatementController;
use App\Vendoring\Controller\Statement\VendorStatementExportController;
use App\Vendoring\Controller\VendorTransactionController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ControllerInfrastructureContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, list<string>}>
     */
    public static function controllerProvider(): iterable
    {
        yield 'vendor_transaction' => [VendorTransactionController::class, ['create', 'listByVendor', 'updateStatus']];
        yield 'vendor_summary' => [VendorSummaryController::class, ['summary']];
        yield 'vendor_metric' => [VendorMetricController::class, ['overview', 'trends']];
        yield 'payout_account' => [PayoutAccountController::class, ['upsert']];
        yield 'payout' => [PayoutController::class, ['create', 'process', 'getOne']];
        yield 'vendor_statement' => [VendorStatementController::class, ['build']];
        yield 'vendor_statement_export' => [VendorStatementExportController::class, ['export']];
    }

    /** @param list<string> $actionMethods */
    #[DataProvider('controllerProvider')]
    public function testControllersExtendAbstractSymfonyController(string $controllerClass, array $actionMethods): void
    {
        self::assertTrue(is_a($controllerClass, AbstractController::class, true));

        self::assertTrue(class_exists($controllerClass));
        /** @var class-string<AbstractController> $controllerClass */
        $reflection = new \ReflectionClass($controllerClass);
        self::assertNotEmpty($reflection->getAttributes(Route::class));

        foreach ($actionMethods as $methodName) {
            self::assertTrue($reflection->hasMethod($methodName));

            $method = $reflection->getMethod($methodName);
            self::assertNotEmpty($method->getAttributes(Route::class));

            $returnType = $method->getReturnType();
            self::assertNotNull($returnType);
            self::assertSame(JsonResponse::class, $returnType instanceof \ReflectionNamedType ? $returnType->getName() : null);
        }
    }
}
