<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeView;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeView;
use App\Vendoring\Projection\Vendor\VendorOwnershipView;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeView;
use App\Vendoring\Service\Ops\VendorRuntimeStatusViewBuilderService;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeViewBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipViewBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeFinanceConsistencyTest extends TestCase
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

    public function testBuildExposesMissingPayoutAccountAndStatementAsFinanceReadinessSignals(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, []));
        $this->finance->expects(self::once())->method('build')->with('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')
            ->willReturn(new VendorFinanceRuntimeView(
                tenantId: 'tenant-1',
                vendorId: '101',
                currency: 'USD',
                ownership: ['ownerUserId' => 5001],
                metricOverview: ['revenue' => 100.0, 'refunds' => 10.0, 'payouts' => 0.0, 'balance' => 90.0],
                payoutAccount: null,
                statement: null,
            ));
        $this->statementDelivery->expects(self::once())->method('build')->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $dto): bool {
            self::assertSame('tenant-1', $dto->tenantId);
            self::assertSame('101', $dto->vendorId);
            self::assertSame('2026-03-01', $dto->from);
            self::assertSame('2026-03-31', $dto->to);
            self::assertSame('USD', $dto->currency);

            return true;
        }))
            ->willReturn(new VendorStatementDeliveryRuntimeView(
                tenantId: 'tenant-1',
                vendorId: '101',
                currency: 'USD',
                ownership: ['ownerUserId' => 5001],
                statement: [],
                export: null,
                recipients: [],
            ));
        $this->externalIntegration->expects(self::once())->method('build')->with('tenant-1', '101')
            ->willReturn(new VendorExternalIntegrationRuntimeView(
                tenantId: 'tenant-1',
                vendorId: '101',
                ownership: ['ownerUserId' => 5001],
                crm: [],
                webhooks: [],
                payoutBridge: [],
                surfaces: [],
            ));

        $payload = (new VendorRuntimeStatusViewBuilderService(
            $this->ownership,
            $this->finance,
            $this->statementDelivery,
            $this->externalIntegration,
        ))->build('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertSame(['revenue' => 100.0, 'refunds' => 10.0, 'payouts' => 0.0, 'balance' => 90.0], $payload['finance']['metricOverview']);
        self::assertNull($payload['finance']['payoutAccount']);
        self::assertNull($payload['finance']['statement']);
        self::assertSame([], $payload['statementDelivery']['recipients']);
        self::assertTrue($payload['surfaceStatus']['finance']);
        self::assertFalse($payload['surfaceStatus']['statementDelivery']);
    }
}
