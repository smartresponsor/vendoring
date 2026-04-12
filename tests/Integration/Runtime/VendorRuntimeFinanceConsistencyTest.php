<?php

declare(strict_types=1);

namespace App\Tests\Integration\Runtime;

use App\Projection\VendorExternalIntegrationRuntimeView;
use App\Projection\VendorFinanceRuntimeView;
use App\Projection\VendorOwnershipView;
use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Projection\VendorStatementDeliveryRuntimeView;
use App\Service\Ops\VendorRuntimeStatusViewBuilder;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeFinanceConsistencyTest extends TestCase
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

        $payload = (new VendorRuntimeStatusViewBuilder(
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
