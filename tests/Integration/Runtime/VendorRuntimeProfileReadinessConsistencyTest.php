<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeProjection;
use App\Vendoring\Projection\Vendor\VendorOwnershipProjection;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeProjection;
use App\Vendoring\Service\Ops\VendorRuntimeStatusProjectionBuilderService;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeProfileReadinessConsistencyTest extends TestCase
{
    private VendorOwnershipProjectionBuilderServiceInterface&MockObject $ownership;
    private VendorFinanceRuntimeProjectionBuilderServiceInterface&MockObject $finance;
    private VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface&MockObject $statementDelivery;
    private VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface&MockObject $externalIntegration;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipProjectionBuilderServiceInterface::class);
        $this->finance = $this->createMock(VendorFinanceRuntimeProjectionBuilderServiceInterface::class);
        $this->statementDelivery = $this->createMock(VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface::class);
        $this->externalIntegration = $this->createMock(VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface::class);
    }

    public function testBuildKeepsIncompleteProfileNotReadyForPublishing(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipProjection(101, 5001, []));
        $this->finance->expects(self::once())->method('build')->willReturn(new VendorFinanceRuntimeProjection(
            tenantId: 'tenant-1',
            vendorId: '101',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            metricOverview: [],
            payoutAccount: null,
            statement: null,
        ));
        $this->statementDelivery->expects(self::once())->method('build')->willReturn(new VendorStatementDeliveryRuntimeProjection(
            tenantId: 'tenant-1',
            vendorId: '101',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            statement: [],
            export: null,
            recipients: [],
        ));
        $this->externalIntegration->expects(self::once())->method('build')->willReturn(new VendorExternalIntegrationRuntimeProjection(
            tenantId: 'tenant-1',
            vendorId: '101',
            ownership: ['ownerUserId' => 5001],
            crm: [],
            webhooks: [],
            payoutBridge: [],
            surfaces: [],
        ));

        $payload = $this->buildRuntimeStatus()->build('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')->toArray();

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
            tenantId: 'tenant-1',
            vendorId: '202',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            metricOverview: [],
            payoutAccount: ['provider' => 'bank'],
            statement: ['closing' => 85.0],
        ));
        $this->statementDelivery->expects(self::once())->method('build')->willReturn(new VendorStatementDeliveryRuntimeProjection(
            tenantId: 'tenant-1',
            vendorId: '202',
            currency: 'USD',
            ownership: ['ownerUserId' => 5001],
            statement: ['closing' => 85.0],
            export: ['path' => '/tmp/statement.pdf'],
            recipients: [['email' => 'billing@example.com']],
        ));
        $this->externalIntegration->expects(self::once())->method('build')->willReturn(new VendorExternalIntegrationRuntimeProjection(
            tenantId: 'tenant-1',
            vendorId: '202',
            ownership: ['ownerUserId' => 5001],
            crm: [],
            webhooks: [],
            payoutBridge: [],
            surfaces: [],
        ));

        $payload = $this->buildRuntimeStatus()->build('tenant-1', '202', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertArrayHasKey('ownership', $payload);
        $ownership = $payload['ownership'] ?? null;
        self::assertIsArray($ownership);
        self::assertSame(5001, $ownership['ownerUserId'] ?? null);
        self::assertTrue($payload['surfaceStatus']['ownership']);
    }

    private function buildRuntimeStatus(): VendorRuntimeStatusProjectionBuilderService
    {
        return new VendorRuntimeStatusProjectionBuilderService(
            $this->ownership,
            $this->finance,
            $this->statementDelivery,
            $this->externalIntegration,
        );
    }
}
