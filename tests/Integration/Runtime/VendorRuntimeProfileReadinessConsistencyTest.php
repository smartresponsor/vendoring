<?php

declare(strict_types=1);

namespace App\Tests\Integration\Runtime;

use App\Projection\VendorExternalIntegrationRuntimeView;
use App\Projection\VendorFinanceRuntimeView;
use App\Projection\VendorOwnershipView;
use App\Projection\VendorProfileView;
use App\Projection\VendorStatementDeliveryRuntimeView;
use App\Service\Ops\VendorRuntimeStatusViewBuilder;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use App\ServiceInterface\VendorProfileViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeProfileReadinessConsistencyTest extends TestCase
{
    private VendorOwnershipViewBuilderInterface&MockObject $ownership;
    private VendorProfileViewBuilderInterface&MockObject $profile;
    private VendorFinanceRuntimeViewBuilderInterface&MockObject $finance;
    private VendorStatementDeliveryRuntimeViewBuilderInterface&MockObject $statementDelivery;
    private VendorExternalIntegrationRuntimeViewBuilderInterface&MockObject $externalIntegration;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipViewBuilderInterface::class);
        $this->profile = $this->createMock(VendorProfileViewBuilderInterface::class);
        $this->finance = $this->createMock(VendorFinanceRuntimeViewBuilderInterface::class);
        $this->statementDelivery = $this->createMock(VendorStatementDeliveryRuntimeViewBuilderInterface::class);
        $this->externalIntegration = $this->createMock(VendorExternalIntegrationRuntimeViewBuilderInterface::class);
    }

    public function testBuildKeepsIncompleteProfileNotReadyForPublishing(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, []));
        $this->profile->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorProfileView(
                vendorId: 101,
                brandName: 'Vendor Brand',
                vendorStatus: 'inactive',
                profile: ['brandName' => 'Vendor Brand'],
                businessProfile: ['ownerUserId' => null],
                publicProfile: ['displayName' => 'Vendor Brand'],
                searchProfile: ['seoTitle' => null],
                publication: ['status' => 'draft', 'publishedAt' => null, 'canPublish' => false],
                sections: [
                    'business' => ['label' => 'Business profile', 'complete' => false, 'missing' => ['ownerUserId']],
                    'public' => ['label' => 'Public profile', 'complete' => false, 'missing' => ['website', 'socials']],
                    'search' => ['label' => 'Search metadata', 'complete' => false, 'missing' => ['seoTitle']],
                ],
                completionPercent: 43,
                readyForPublishing: false,
                nextAction: 'assign_owner',
            ));
        $this->finance->expects(self::once())->method('build')->willReturn(new VendorFinanceRuntimeView(
            tenantId: 'tenant-1',
            vendorId: '101',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            metricOverview: [],
            payoutAccount: null,
            statement: null,
        ));
        $this->statementDelivery->expects(self::once())->method('build')->willReturn(new VendorStatementDeliveryRuntimeView(
            tenantId: 'tenant-1',
            vendorId: '101',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            statement: [],
            export: null,
            recipients: [],
        ));
        $this->externalIntegration->expects(self::once())->method('build')->willReturn(new VendorExternalIntegrationRuntimeView(
            tenantId: 'tenant-1',
            vendorId: '101',
            ownership: ['ownerUserId' => 5001],
            crm: [],
            webhooks: [],
            payoutBridge: [],
            surfaces: [],
        ));

        $payload = $this->buildRuntimeStatus()->build('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertFalse($payload['profile']['readyForPublishing']);
        self::assertSame('assign_owner', $payload['profile']['nextAction']);
        self::assertSame(43, $payload['profile']['completionPercent']);
        self::assertFalse($payload['profile']['sections']['business']['complete']);
        self::assertFalse($payload['profile']['sections']['public']['complete']);
        self::assertFalse($payload['profile']['sections']['search']['complete']);
        self::assertTrue($payload['surfaceStatus']['profile']);
    }

    public function testBuildKeepsCompleteProfileReadyForPublishing(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(202)
            ->willReturn(new VendorOwnershipView(202, 5001, []));
        $this->profile->expects(self::once())->method('buildForVendorId')->with(202)
            ->willReturn(new VendorProfileView(
                vendorId: 202,
                brandName: 'Vendor Brand',
                vendorStatus: 'active',
                profile: ['brandName' => 'Vendor Brand'],
                businessProfile: ['ownerUserId' => 5001],
                publicProfile: ['displayName' => 'Vendor Public'],
                searchProfile: ['seoTitle' => 'Vendor SEO'],
                publication: ['status' => 'published', 'publishedAt' => '2026-03-31T10:00:00+00:00', 'canPublish' => true],
                sections: [
                    'business' => ['label' => 'Business profile', 'complete' => true, 'missing' => []],
                    'public' => ['label' => 'Public profile', 'complete' => true, 'missing' => []],
                    'search' => ['label' => 'Search metadata', 'complete' => true, 'missing' => []],
                ],
                completionPercent: 100,
                readyForPublishing: true,
                nextAction: null,
            ));
        $this->finance->expects(self::once())->method('build')->willReturn(new VendorFinanceRuntimeView(
            tenantId: 'tenant-1',
            vendorId: '202',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            metricOverview: [],
            payoutAccount: ['provider' => 'bank'],
            statement: ['closing' => 85.0],
        ));
        $this->statementDelivery->expects(self::once())->method('build')->willReturn(new VendorStatementDeliveryRuntimeView(
            tenantId: 'tenant-1',
            vendorId: '202',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            statement: ['closing' => 85.0],
            export: ['path' => '/tmp/statement.pdf'],
            recipients: [['email' => 'billing@example.com']],
        ));
        $this->externalIntegration->expects(self::once())->method('build')->willReturn(new VendorExternalIntegrationRuntimeView(
            tenantId: 'tenant-1',
            vendorId: '202',
            ownership: ['ownerUserId' => 5001],
            crm: [],
            webhooks: [],
            payoutBridge: [],
            surfaces: [],
        ));

        $payload = $this->buildRuntimeStatus()->build('tenant-1', '202', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertTrue($payload['profile']['readyForPublishing']);
        self::assertNull($payload['profile']['nextAction']);
        self::assertSame(100, $payload['profile']['completionPercent']);
        self::assertTrue($payload['profile']['sections']['business']['complete']);
        self::assertTrue($payload['profile']['sections']['public']['complete']);
        self::assertTrue($payload['profile']['sections']['search']['complete']);
        self::assertSame('published', $payload['profile']['publication']['status']);
        self::assertTrue($payload['surfaceStatus']['profile']);
    }

    private function buildRuntimeStatus(): VendorRuntimeStatusViewBuilder
    {
        return new VendorRuntimeStatusViewBuilder(
            $this->ownership,
            $this->profile,
            $this->finance,
            $this->statementDelivery,
            $this->externalIntegration,
        );
    }
}
