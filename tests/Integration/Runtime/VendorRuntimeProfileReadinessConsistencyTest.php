<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeView;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeView;
use App\Vendoring\Projection\Vendor\VendorOwnershipView;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeView;
use App\Vendoring\Service\Ops\VendorRuntimeStatusViewBuilderService;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipViewBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeProfileReadinessConsistencyTest extends TestCase
{
    private VendorOwnershipViewBuilderServiceInterface&MockObject $ownership;
    private VendorFinanceRuntimeViewBuilderServiceInterface&MockObject $finance;
    private VendorStatementDeliveryRuntimeViewBuilderServiceInterface&MockObject $statementDelivery;
    private VendorExternalIntegrationRuntimeViewBuilderServiceInterface&MockObject $externalIntegration;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipViewBuilderServiceInterface::class);
        $this->finance = $this->createMock(VendorFinanceRuntimeViewBuilderServiceInterface::class);
        $this->statementDelivery = $this->createMock(VendorStatementDeliveryRuntimeViewBuilderServiceInterface::class);
        $this->externalIntegration = $this->createMock(VendorExternalIntegrationRuntimeViewBuilderServiceInterface::class);
    }

    public function testBuildKeepsIncompleteProfileNotReadyForPublishing(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, []));
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
            ->willReturn(new VendorOwnershipView(202, 5001, []));
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

        self::assertArrayHasKey('ownership', $payload);
        $ownership = $payload['ownership'] ?? null;
        self::assertIsArray($ownership);
        self::assertSame(5001, $ownership['ownerUserId'] ?? null);
        self::assertTrue($payload['surfaceStatus']['ownership']);
    }

    private function buildRuntimeStatus(): VendorRuntimeStatusViewBuilderService
    {
        return new VendorRuntimeStatusViewBuilderService(
            $this->ownership,
            $this->finance,
            $this->statementDelivery,
            $this->externalIntegration,
        );
    }
}
