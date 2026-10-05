<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Builder\Ops\VendorRuntimeStatusProjectionBuilder;
use App\Vendoring\BuilderInterface\Finance\VendorFinanceRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderInterface;
use App\Vendoring\Projection\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\Projection\VendorFinanceRuntimeProjection;
use App\Vendoring\Projection\VendorOwnershipProjection;
use App\Vendoring\Projection\VendorStatementDeliveryRuntimeProjection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeProfileReadinessConsistencyTest extends TestCase
{
    private VendorOwnershipProjectionBuilderInterface&MockObject $ownership;
    private VendorFinanceRuntimeProjectionBuilderInterface&MockObject $finance;
    private VendorStatementDeliveryRuntimeProjectionBuilderInterface&MockObject $statementDelivery;
    private VendorExternalIntegrationRuntimeProjectionBuilderInterface&MockObject $externalIntegration;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipProjectionBuilderInterface::class);
        $this->finance = $this->createMock(VendorFinanceRuntimeProjectionBuilderInterface::class);
        $this->statementDelivery = $this->createMock(VendorStatementDeliveryRuntimeProjectionBuilderInterface::class);
        $this->externalIntegration = $this->createMock(VendorExternalIntegrationRuntimeProjectionBuilderInterface::class);
    }

    public function testBuildKeepsIncompleteProfileNotReadyForPublishing(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipProjection(101, 5001, []));
        $this->finance->expects(self::once())->method('build')->willReturn(new VendorFinanceRuntimeProjection(
            vendorId: '101',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            metricOverview: [],
            payoutAccount: null,
            statement: null,
        ));
        $this->statementDelivery->expects(self::once())->method('build')->willReturn(new VendorStatementDeliveryRuntimeProjection(
            vendorId: '101',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            statement: [],
            export: null,
            recipients: [],
        ));
        $this->externalIntegration->expects(self::once())->method('build')->willReturn(new VendorExternalIntegrationRuntimeProjection(
            vendorId: '101',
            ownership: ['ownerUserId' => 5001],
            crm: [],
            webhooks: [],
            payoutBridge: [],
            surfaces: [],
        ));

        $payload = $this->buildRuntimeStatus()->build('101', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertArrayHasKey('ownership', $payload);
        $ownership = $payload['ownership'] ?? null;
        self::assertIsArray($ownership);
        self::assertSame(5001, $ownership['ownerUserId'] ?? null);
        self::assertFalse($payload['surfaceStatus']['statementDelivery']);
        self::assertTrue($payload['surfaceStatus']['finance']);
    }

    public function testBuildKeepsCompleteProfileReadyForPublishing(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(202)
            ->willReturn(new VendorOwnershipProjection(202, 5001, []));
        $this->finance->expects(self::once())->method('build')->willReturn(new VendorFinanceRuntimeProjection(
            vendorId: '202',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            metricOverview: [],
            payoutAccount: ['provider' => 'bank'],
            statement: ['closing' => 85.0],
        ));
        $this->statementDelivery->expects(self::once())->method('build')->willReturn(new VendorStatementDeliveryRuntimeProjection(
            vendorId: '202',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            statement: ['closing' => 85.0],
            export: ['path' => '/tmp/statement.pdf'],
            recipients: [['email' => 'billing@example.com']],
        ));
        $this->externalIntegration->expects(self::once())->method('build')->willReturn(new VendorExternalIntegrationRuntimeProjection(
            vendorId: '202',
            ownership: ['ownerUserId' => 5001],
            crm: [],
            webhooks: [],
            payoutBridge: [],
            surfaces: [],
        ));

        $payload = $this->buildRuntimeStatus()->build('202', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertArrayHasKey('ownership', $payload);
        $ownership = $payload['ownership'] ?? null;
        self::assertIsArray($ownership);
        self::assertSame(5001, $ownership['ownerUserId'] ?? null);
        self::assertTrue($payload['surfaceStatus']['ownership']);
    }

    private function buildRuntimeStatus(): VendorRuntimeStatusProjectionBuilder
    {
        return new VendorRuntimeStatusProjectionBuilder(
            $this->ownership,
            $this->finance,
            $this->statementDelivery,
            $this->externalIntegration,
        );
    }
}
