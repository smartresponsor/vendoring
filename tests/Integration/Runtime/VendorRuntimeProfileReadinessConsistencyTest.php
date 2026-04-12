<?php

declare(strict_types=1);

namespace App\Tests\Integration\Runtime;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Projection\VendorExternalIntegrationRuntimeView;
use App\Projection\VendorFinanceRuntimeView;
use App\Projection\VendorOwnershipView;
use App\Projection\VendorStatementDeliveryRuntimeView;
use App\Service\Ops\VendorRuntimeStatusViewBuilder;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeProfileReadinessConsistencyTest extends TestCase
{
    private VendorOwnershipViewBuilderInterface&MockObject $ownership;
    private VendorFinanceRuntimeViewBuilderInterface&MockObject $finance;
    private VendorStatementDeliveryRuntimeViewBuilderInterface&MockObject $statementDelivery;
    private VendorExternalIntegrationRuntimeViewBuilderInterface&MockObject $externalIntegration;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipViewBuilderInterface::class);
        $this->finance = $this->createMock(VendorFinanceRuntimeViewBuilderInterface::class);
        $this->statementDelivery = $this->createMock(VendorStatementDeliveryRuntimeViewBuilderInterface::class);
        $this->externalIntegration = $this->createMock(VendorExternalIntegrationRuntimeViewBuilderInterface::class);
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
        self::assertIsArray($payload['ownership']);
        self::assertSame(5001, $payload['ownership']['ownerUserId']);
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
        self::assertIsArray($payload['ownership']);
        self::assertSame(5001, $payload['ownership']['ownerUserId']);
        self::assertIsArray($payload['finance']);
        self::assertIsArray($payload['statementDelivery']);
        self::assertTrue($payload['surfaceStatus']['ownership']);
    }

    private function buildRuntimeStatus(): VendorRuntimeStatusViewBuilder
    {
        return new VendorRuntimeStatusViewBuilder(
            $this->ownership,
            $this->finance,
            $this->statementDelivery,
            $this->externalIntegration,
        );
    }
}
