<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ops;

use PHPUnit\Framework\TestCase;

final class VendorTransactionOperatorSurfaceContractTest extends TestCase
{
    public function testTwigOperatorTemplateShowsVendorIdentityAsReadonlyAndUsesStatusLabels(): void
    {
        $template = (string) file_get_contents(dirname(__DIR__, 3).'/templates/ops/vendor_transactions/index.html.twig');

        self::assertStringNotContainsString('createForm.vendorId', $template);
        self::assertStringContainsString('value="{{ vendorId }}" readonly disabled', $template);
        self::assertStringContainsString('statusLabels[transaction.status]', $template);
    }

    public function testOperatorHttpServiceDoesNotSubmitVendorIdInCreateForm(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3).'/src/Service/Http/Vendor/Transaction/Operator/VendorTransactionOperatorService.php');

        self::assertStringContainsString('use Twig\\Environment;', $source);
        self::assertStringContainsString('private readonly Environment $twig', $source);
        self::assertStringContainsString('$this->twig->render(', $source);
        self::assertFalse(get_parent_class(App\Vendoring\Service\Http\Vendor\Transaction\Operator\VendorTransactionOperatorService::class));
    }

    public function testHttpServiceCreatesTransactionsUsingRouteScopedVendorId(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3).'/src/Service/Http/Vendor/Transaction/Operator/VendorTransactionOperatorService.php');

        self::assertStringContainsString('vendorId: $vendorId,', $source);
        self::assertStringNotContainsString('vendorId: trim($input->vendorId)', $source);
        self::assertStringContainsString("'statusLabels' => VendorTransactionStatusValueObject::labels()", $source);
        self::assertNotSame('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController', get_parent_class(App\Vendoring\Service\Http\Vendor\Transaction\Operator\VendorTransactionOperatorService::class));
    }
}
