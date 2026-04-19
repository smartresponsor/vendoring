<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ops;

use App\Vendoring\Projection\VendorRuntimeStatusView;
use App\Vendoring\Service\Ops\VendorReleaseBaselineReader;
use App\Vendoring\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorReleaseBaselineReaderTest extends TestCase
{
    private VendorRuntimeStatusViewBuilderInterface&MockObject $runtimeStatusViewBuilder;

    protected function setUp(): void
    {
        $this->runtimeStatusViewBuilder = $this->createMock(VendorRuntimeStatusViewBuilderInterface::class);
    }

    public function testBuildAddsProfileSummaryAndProfileIssueWhenIncomplete(): void
    {
        $this->runtimeStatusViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')
            ->willReturn(new VendorRuntimeStatusView(
                tenantId: 'tenant-1',
                vendorId: '42',
                currency: 'USD',
                ownership: ['ownerUserId' => 7],
                finance: ['metricOverview' => []],
                statementDelivery: ['statement' => []],
                externalIntegration: ['surfaces' => []],
                surfaceStatus: [
                    'ownership' => true,
                    'finance' => false,
                    'statementDelivery' => true,
                    'externalIntegration' => true,
                ],
                generatedAt: '2025-01-31T00:00:00+00:00',
            ));

        $payload = $this->buildReader()->build('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')->toArray();

        self::assertSame('warn', $payload['status']);
        self::assertContains('surface.finance.missing', $payload['issues']);
        self::assertTrue($payload['artifactStatus']['runtimeStatusCommand']);
    }

    public function testBuildMarksOwnershipSurfaceUnavailableWhenRuntimeStatusHasNoOwnership(): void
    {
        $this->runtimeStatusViewBuilder
            ->expects(self::once())
            ->method('build')
            ->willReturn(new VendorRuntimeStatusView(
                tenantId: 'tenant-1',
                vendorId: 'vendor-abc',
                currency: 'USD',
                ownership: null,
                finance: [],
                statementDelivery: [],
                externalIntegration: [],
                surfaceStatus: [
                    'ownership' => false,
                    'finance' => false,
                    'statementDelivery' => false,
                    'externalIntegration' => false,
                ],
                generatedAt: '2025-01-31T00:00:00+00:00',
            ));

        $payload = $this->buildReader()->build('tenant-1', 'vendor-abc')->toArray();

        self::assertNull($payload['runtimeStatus']['ownership']);
        self::assertContains('surface.ownership.missing', $payload['issues']);
        self::assertContains('surface.finance.missing', $payload['issues']);
    }

    private function buildReader(): VendorReleaseBaselineReader
    {
        return new VendorReleaseBaselineReader($this->runtimeStatusViewBuilder);
    }
}
