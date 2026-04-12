<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('statement_params_required', $payload['error'] ?? null);
        self::assertSame('Provide tenantId, from, and to query parameters.', $payload['hint'] ?? null);
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
