<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Ops;

use App\Vendoring\Projection\Vendor\VendorRuntimeStatusProjection;
use App\Vendoring\Service\Ops\VendorReleaseBaselineReaderService;
use App\Vendoring\ServiceInterface\Ops\VendorRuntimeStatusProjectionBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorReleaseBaselineRuntimeConsistencyTest extends TestCase
{
    private VendorRuntimeStatusProjectionBuilderServiceInterface&MockObject $runtimeStatus;

    protected function setUp(): void
    {
        $this->runtimeStatus = $this->createMock(VendorRuntimeStatusProjectionBuilderServiceInterface::class);
    }

    public function testBuildMarksBaselineWarnWhenRuntimeSurfacesAreMissing(): void
    {
        $this->runtimeStatus
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')
            ->willReturn(new VendorRuntimeStatusProjection(
                tenantId: 'tenant-1',
                vendorId: '101',
                currency: 'USD',
                ownership: null,
                finance: ['metricOverview' => []],
                statementDelivery: [],
                externalIntegration: ['surfaces' => []],
                surfaceStatus: [
                    'ownership' => false,
                    'finance' => true,
                    'statementDelivery' => false,
                    'externalIntegration' => true,
                ],
                generatedAt: '2026-03-31T10:00:00+00:00',
            ));

        $payload = (new VendorReleaseBaselineReaderService($this->runtimeStatus))
            ->build('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')
            ->toArray();

        self::assertSame('warn', $payload['status']);
        self::assertContains('surface.ownership.missing', $payload['issues']);
        self::assertContains('surface.statementDelivery.missing', $payload['issues']);
        self::assertSame('warn', $payload['status']);
        self::assertArrayHasKey('runtimeStatus', $payload);
    }

    public function testBuildMarksProfileSummaryUnavailableWhenProfileSurfaceIsNull(): void
    {
        $this->runtimeStatus
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', 'vendor-alpha', null, null, 'EUR')
            ->willReturn(new VendorRuntimeStatusProjection(
                tenantId: 'tenant-1',
                vendorId: 'vendor-alpha',
                currency: 'EUR',
                ownership: null,
                finance: ['metricOverview' => []],
                statementDelivery: ['recipients' => []],
                externalIntegration: ['surfaces' => []],
                surfaceStatus: [
                    'ownership' => false,
                    'finance' => true,
                    'statementDelivery' => true,
                    'externalIntegration' => true,
                ],
                generatedAt: '2026-03-31T10:00:00+00:00',
            ));

        $payload = (new VendorReleaseBaselineReaderService($this->runtimeStatus))
            ->build('tenant-1', 'vendor-alpha', null, null, 'EUR')
            ->toArray();

        self::assertSame('warn', $payload['status']);
        self::assertArrayHasKey('runtimeStatus', $payload);
        self::assertContains('surface.ownership.missing', $payload['issues']);
    }
}
