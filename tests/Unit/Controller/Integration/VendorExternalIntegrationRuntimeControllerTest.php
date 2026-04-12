<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Integration;

use App\Controller\Integration\VendorExternalIntegrationRuntimeController;
use App\Projection\VendorExternalIntegrationRuntimeView;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorExternalIntegrationRuntimeControllerTest extends TestCase
{
    public function testShowReturnsValidationErrorWhenTenantIdIsMissing(): void
    {
        $builder = $this->createMock(VendorExternalIntegrationRuntimeViewBuilderInterface::class);
        $builder->expects(self::never())->method('build');

        $controller = new VendorExternalIntegrationRuntimeController($builder);
        $response = $controller->show('vendor-1', new Request());
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('tenant_id_required', $payload['error'] ?? null);
        self::assertSame('Provide the tenantId query parameter.', $payload['hint'] ?? null);
    }

    public function testShowReturnsRuntimeProjectionWhenTenantIdIsProvided(): void
    {
        $builder = $this->createMock(VendorExternalIntegrationRuntimeViewBuilderInterface::class);
        $builder->expects(self::once())
            ->method('build')
            ->with('tenant-1', 'vendor-1')
            ->willReturn(new VendorExternalIntegrationRuntimeView(
                tenantId: 'tenant-1',
                vendorId: 'vendor-1',
                ownership: null,
                crm: ['status' => 'connected'],
                webhooks: ['ready' => true],
                payoutBridge: ['ready' => true],
                surfaces: ['crm', 'webhooks'],
            ));

        $controller = new VendorExternalIntegrationRuntimeController($builder);
        $response = $controller->show('vendor-1', new Request(['tenantId' => 'tenant-1']));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('connected', (string) $response->getContent());
    }
}
