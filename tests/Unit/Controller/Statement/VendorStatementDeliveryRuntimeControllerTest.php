<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Controller\Statement;

use App\Controller\Statement\VendorStatementDeliveryRuntimeController;
use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Projection\VendorStatementDeliveryRuntimeView;
use App\Service\Statement\VendorStatementRequestResolver;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorStatementDeliveryRuntimeControllerTest extends TestCase
{
    private VendorStatementDeliveryRuntimeViewBuilderInterface&MockObject $builder;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(VendorStatementDeliveryRuntimeViewBuilderInterface::class);
    }

    public function testShowReturnsValidationErrorWhenParamsMissing(): void
    {
        $controller = new VendorStatementDeliveryRuntimeController($this->builder, new VendorStatementRequestResolver());

        $response = $controller->show('vendor-1', new Request());
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('statement_runtime_params_required', $payload['error'] ?? null);
        self::assertSame('Provide tenantId, from, and to query parameters.', $payload['hint'] ?? null);
    }

    public function testShowBuildsRuntimeViewFromResolvedRequest(): void
    {
        $this->builder->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $request): bool {
                return 'tenant-1' === $request->tenantId
                    && 'vendor-1' === $request->vendorId
                    && '2026-03-01' === $request->from
                    && '2026-03-31' === $request->to
                    && 'USD' === $request->currency
                    && false === $request->includeExport;
            }))
            ->willReturn(new VendorStatementDeliveryRuntimeView(
                tenantId: 'tenant-1',
                vendorId: 'vendor-1',
                currency: 'USD',
                ownership: null,
                statement: ['closing' => 10.0],
                export: null,
                recipients: [],
            ));

        $controller = new VendorStatementDeliveryRuntimeController($this->builder, new VendorStatementRequestResolver());
        $response = $controller->show('vendor-1', new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'includeExport' => 'false',
        ]));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('tenant-1', (string) $response->getContent());
        self::assertStringContainsString('closing', (string) $response->getContent());
    }
}
