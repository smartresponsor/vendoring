<?php

declare(strict_types=1);

namespace App\Tests\Integration\Ops;

use App\Projection\VendorRuntimeStatusView;
use App\Service\Ops\VendorReleaseBaselineReader;
use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorReleaseBaselineRuntimeConsistencyTest extends TestCase
{
    private VendorRuntimeStatusViewBuilderInterface&MockObject $runtimeStatus;

    protected function setUp(): void
    {
        $this->runtimeStatus = $this->createMock(VendorRuntimeStatusViewBuilderInterface::class);
    }

    public function testBuildMarksBaselineWarnWhenRuntimeSurfacesAreMissing(): void
    {
        $this->runtimeStatus
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')
            ->willReturn(new VendorRuntimeStatusView(
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

        $payload = (new VendorReleaseBaselineReader($this->runtimeStatus))
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
            ->willReturn(new VendorRuntimeStatusView(
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

        $payload = (new VendorReleaseBaselineReader($this->runtimeStatus))
            ->build('tenant-1', 'vendor-alpha', null, null, 'EUR')
            ->toArray();

        self::assertSame('warn', $payload['status']);
        self::assertArrayHasKey('runtimeStatus', $payload);
        self::assertContains('surface.ownership.missing', $payload['issues']);
    }
}
