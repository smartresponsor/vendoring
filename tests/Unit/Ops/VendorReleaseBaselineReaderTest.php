<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ops;

use App\Projection\VendorRuntimeStatusView;
use App\Service\Ops\VendorReleaseBaselineReader;
use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
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
                profile: [
                    'completionPercent' => 75,
                    'readyForPublishing' => false,
                    'nextAction' => 'assign_owner',
                ],
                finance: ['metricOverview' => []],
                statementDelivery: ['statement' => []],
                externalIntegration: ['surfaces' => []],
                surfaceStatus: [
                    'ownership' => true,
                    'profile' => true,
                    'finance' => true,
                    'statementDelivery' => true,
                    'externalIntegration' => true,
                ],
                generatedAt: '2025-01-31T00:00:00+00:00',
            ));

        $payload = $this->buildReader()->build('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')->toArray();

        self::assertSame(75, $payload['profileSummary']['completionPercent']);
        self::assertFalse($payload['profileSummary']['readyForPublishing']);
        self::assertSame('assign_owner', $payload['profileSummary']['nextAction']);
        self::assertContains('profile.assign_owner.required', $payload['issues']);
    }

    public function testBuildMarksProfileSummaryUnavailableWhenRuntimeStatusHasNoProfile(): void
    {
        $this->runtimeStatusViewBuilder
            ->expects(self::once())
            ->method('build')
            ->willReturn(new VendorRuntimeStatusView(
                tenantId: 'tenant-1',
                vendorId: 'vendor-abc',
                currency: 'USD',
                ownership: null,
                profile: null,
                finance: [],
                statementDelivery: [],
                externalIntegration: [],
                surfaceStatus: [
                    'ownership' => false,
                    'profile' => false,
                    'finance' => false,
                    'statementDelivery' => false,
                    'externalIntegration' => false,
                ],
                generatedAt: '2025-01-31T00:00:00+00:00',
            ));

        $payload = $this->buildReader()->build('tenant-1', 'vendor-abc')->toArray();

        self::assertFalse($payload['profileSummary']['available']);
        self::assertNull($payload['profileSummary']['completionPercent']);
        self::assertContains('surface.profile.missing', $payload['issues']);
    }

    private function buildReader(): VendorReleaseBaselineReader
    {
        return new VendorReleaseBaselineReader($this->runtimeStatusViewBuilder);
    }
}
