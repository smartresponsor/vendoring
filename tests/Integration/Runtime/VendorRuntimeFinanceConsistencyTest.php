<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Builder\Ops\VendorRuntimeStatusProjectionBuilder;
use App\Vendoring\BuilderInterface\Finance\VendorFinanceRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderInterface;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\Projection\VendorFinanceRuntimeProjection;
use App\Vendoring\Projection\VendorOwnershipProjection;
use App\Vendoring\Projection\VendorStatementDeliveryRuntimeProjection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeFinanceConsistencyTest extends TestCase
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

    public function testBuildExposesMissingPayoutAccountAndStatementAsFinanceReadinessSignals(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipProjection(101, 5001, []));
        $this->finance->expects(self::once())->method('build')->with('101', '2026-03-01', '2026-03-31', 'USD')
            ->willReturn(new VendorFinanceRuntimeProjection(
                vendorId: '101',
                currency: 'USD',
                ownership: ['ownerUserId' => 5001],
                metricOverview: ['revenue' => 100.0, 'refunds' => 10.0, 'payouts' => 0.0, 'balance' => 90.0],
                payoutAccount: null,
                statement: null,
            ));
        $this->statementDelivery->expects(self::once())->method('build')->with(self::callback(function (VendorStatementDeliveryRuntimeRequestDTO $dto): bool {
            self::assertSame('101', $dto->vendorId);
            self::assertSame('2026-03-01', $dto->from);
            self::assertSame('2026-03-31', $dto->to);
            self::assertSame('USD', $dto->currency);

            return true;
        }))
            ->willReturn(new VendorStatementDeliveryRuntimeProjection(
                vendorId: '101',
                currency: 'USD',
                ownership: ['ownerUserId' => 5001],
                statement: [],
                export: null,
                recipients: [],
            ));
        $this->externalIntegration->expects(self::once())->method('build')->with('101')
            ->willReturn(new VendorExternalIntegrationRuntimeProjection(
                vendorId: '101',
                ownership: ['ownerUserId' => 5001],
                crm: [],
                webhooks: [],
                payoutBridge: [],
                surfaces: [],
            ));

        $payload = (new VendorRuntimeStatusProjectionBuilder(
            $this->ownership,
            $this->finance,
            $this->statementDelivery,
            $this->externalIntegration,
        ))->build('101', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertSame(['revenue' => 100.0, 'refunds' => 10.0, 'payouts' => 0.0, 'balance' => 90.0], $payload['finance']['metricOverview']);
        self::assertNull($payload['finance']['payoutAccount']);
        self::assertNull($payload['finance']['statement']);
        self::assertSame([], $payload['statementDelivery']['recipients']);
        self::assertTrue($payload['surfaceStatus']['finance']);
        self::assertFalse($payload['surfaceStatus']['statementDelivery']);
    }
}
