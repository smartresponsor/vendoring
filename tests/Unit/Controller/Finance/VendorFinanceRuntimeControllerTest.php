<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Controller\Finance;

use App\Vendoring\Controller\Vendor\VendorFinanceRuntimeController;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\DTO\Api\VendorTenantQueryRequestDTO;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeView;
use App\Vendoring\ServiceInterface\Api\VendorTenantQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeViewBuilderServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorFinanceRuntimeControllerTest extends TestCase
{
    public function testFinanceReturnsValidationErrorWhenTenantIdIsMissing(): void
    {
        $builder = $this->createMock(VendorFinanceRuntimeViewBuilderServiceInterface::class);
        $builder->expects(self::never())->method('build');
        $resolver = $this->createMock(VendorTenantQueryRequestResolverServiceInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->willThrowException(VendorApiQueryValidationException::fromConstraintMessage('tenant_id_required'));

        $controller = new VendorFinanceRuntimeController($builder, $resolver);
        $response = $controller->finance('vendor-1', new Request());
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('tenant_id_required', self::payloadString($payload, 'error'));
        self::assertSame('Provide the tenantId query parameter.', self::payloadString($payload, 'hint'));
    }

    public function testFinanceReturnsDataPayloadWhenTenantIdIsProvided(): void
    {
        $builder = $this->createMock(VendorFinanceRuntimeViewBuilderServiceInterface::class);
        $resolver = $this->createMock(VendorTenantQueryRequestResolverServiceInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->willReturn(new VendorTenantQueryRequestDTO('tenant-1'));
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

        $controller = new VendorFinanceRuntimeController($builder, $resolver);
        $response = $controller->finance('vendor-1', new Request(['tenantId' => 'tenant-1']));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('tenant-1', (string) $response->getContent());
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
