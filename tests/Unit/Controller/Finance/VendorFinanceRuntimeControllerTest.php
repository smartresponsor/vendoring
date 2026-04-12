<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Finance;

use App\Controller\Finance\VendorFinanceRuntimeController;
use App\Projection\VendorFinanceRuntimeView;
use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorFinanceRuntimeControllerTest extends TestCase
{
    public function testFinanceReturnsValidationErrorWhenTenantIdIsMissing(): void
    {
        $builder = $this->createMock(VendorFinanceRuntimeViewBuilderInterface::class);
        $builder->expects(self::never())->method('build');

        $controller = new VendorFinanceRuntimeController($builder);
        $response = $controller->finance('vendor-1', new Request());
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('tenant_id_required', $payload['error'] ?? null);
        self::assertSame('Provide the tenantId query parameter.', $payload['hint'] ?? null);
    }

    public function testFinanceReturnsDataPayloadWhenTenantIdIsProvided(): void
    {
        $builder = $this->createMock(VendorFinanceRuntimeViewBuilderInterface::class);
        $builder->expects(self::once())
            ->method('build')
            ->with('tenant-1', 'vendor-1', null, null, 'USD')
            ->willReturn(new VendorFinanceRuntimeView(
                tenantId: 'tenant-1',
                vendorId: 'vendor-1',
                currency: 'USD',
                ownership: null,
                metricOverview: ['gmv' => 120.0],
                payoutAccount: null,
                statement: null,
            ));

        $controller = new VendorFinanceRuntimeController($builder);
        $response = $controller->finance('vendor-1', new Request(['tenantId' => 'tenant-1']));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('tenant-1', (string) $response->getContent());
    }
}
