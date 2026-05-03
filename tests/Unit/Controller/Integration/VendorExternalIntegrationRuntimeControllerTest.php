<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Controller\Integration;

use App\Vendoring\Controller\Vendor\VendorExternalIntegrationRuntimeController;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\DTO\Api\VendorTenantQueryRequestDTO;
use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\ServiceInterface\Api\VendorTenantQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorExternalIntegrationRuntimeControllerTest extends TestCase
{
    public function testShowReturnsValidationErrorWhenTenantIdIsMissing(): void
    {
        $builder = $this->createMock(VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface::class);
        $builder->expects(self::never())->method('build');
        $resolver = $this->createMock(VendorTenantQueryRequestResolverServiceInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->willThrowException(VendorApiQueryValidationException::fromConstraintMessage('tenant_id_required'));

        $controller = new VendorExternalIntegrationRuntimeController($builder, $resolver);
        $response = $controller->show('vendor-1', new Request());
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('tenant_id_required', self::payloadString($payload, 'error'));
        self::assertSame('Provide the tenantId query parameter.', self::payloadString($payload, 'hint'));
    }

    public function testShowReturnsRuntimeProjectionWhenTenantIdIsProvided(): void
    {
        $builder = $this->createMock(VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface::class);
        $resolver = $this->createMock(VendorTenantQueryRequestResolverServiceInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new VendorTenantQueryRequestDTO('tenant-1'));
        $builder->expects(self::once())
            ->method('build')
            ->with('tenant-1', 'vendor-1')
            ->willReturn(new VendorExternalIntegrationRuntimeProjection(
                tenantId: 'tenant-1',
                vendorId: 'vendor-1',
                ownership: null,
                crm: ['status' => 'connected'],
                webhooks: ['ready' => true],
                payoutBridge: ['ready' => true],
                surfaces: ['crm', 'webhooks'],
            ));

        $controller = new VendorExternalIntegrationRuntimeController($builder, $resolver);
        $response = $controller->show('vendor-1', new Request(['tenantId' => 'tenant-1']));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('connected', (string) $response->getContent());
    }
    /**
     * @return array<string, mixed>
     */
    private static function decodePayload(\Symfony\Component\HttpFoundation\JsonResponse $response): array
    {
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    private static function payloadString(mixed $payload, string $key): ?string
    {
        if (!is_array($payload)) {
            return null;
        }

        $value = $payload[$key] ?? null;

        return is_string($value) ? $value : null;
    }

}
