<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ops;

use PHPUnit\Framework\TestCase;

final class VendorTransactionOperatorSurfaceContractTest extends TestCase
{
    public function testTwigOperatorTemplateShowsVendorIdentityAsReadonlyAndUsesStatusLabels(): void
    {
        $template = (string) file_get_contents(dirname(__DIR__, 3) . '/templates/ops/vendor_transactions/index.html.twig');

        self::assertStringNotContainsString('createForm.vendorId', $template);
        self::assertStringContainsString('value="{{ vendorId }}" readonly disabled', $template);
        self::assertStringContainsString('statusLabels[transaction.status]', $template);
    }

    public function testFallbackOperatorSurfaceDoesNotSubmitVendorIdInCreateForm(): void
    {
        $builder = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Service/Ops/VendorTransactionOperatorPageBuilder.php');

        self::assertStringContainsString('id="vendorIdDisplay"', $builder);
        self::assertStringNotContainsString('name="vendorId"', $builder);
        self::assertStringContainsString('readonly disabled', $builder);
    }

    public function testControllerCreatesTransactionsUsingRouteScopedVendorId(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Controller/Ops/VendorTransactionOperatorController.php');

        self::assertStringContainsString('vendorId: $vendorId,', $controller);
        self::assertStringNotContainsString('vendorId: trim($input->vendorId)', $controller);
        self::assertStringContainsString("'statusLabels' => VendorTransactionStatus::labels()", $controller);
    }
}
