<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Payout;

use App\Controller\Payout\VendorStatementController;
use App\Service\Statement\VendorStatementRequestResolver;
use App\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementControllerTest extends TestCase
{
    public function testBuildReturnsValidationErrorWhenParamsMissing(): void
    {
        $controller = new VendorStatementController(new FakeVendorStatementService(['items' => []]), new VendorStatementRequestResolver());

        $response = $controller->build('vendor-1', new Request());

        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString('params required', (string) $response->getContent());
    }

    public function testBuildReturnsStatementPayload(): void
    {
        $service = new FakeVendorStatementService([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'closing' => 120.5,
            'items' => [
                ['type' => 'earnings', 'amount' => 120.5, 'currency' => 'USD'],
            ],
        ]);

        $controller = new VendorStatementController($service, new VendorStatementRequestResolver());
        $request = new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
        ]);

        $response = $controller->build('vendor-1', $request);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('tenant-1', (string) $response->getContent());
        self::assertStringContainsString('120.5', (string) $response->getContent());
    }
}
