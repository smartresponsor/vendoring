<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Projection\Vendor\VendorExternalIntegrationRuntimeProjection;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeProjection;
use App\Vendoring\Projection\Vendor\VendorOwnershipProjection;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeProjection;
use App\Vendoring\Service\Ops\VendorRuntimeStatusProjectionBuilderService;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeFinanceConsistencyTest extends TestCase
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

    public function testBuildExposesMissingPayoutAccountAndStatementAsFinanceReadinessSignals(): void
    {
        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipProjection(101, 5001, []));
        $this->finance->expects(self::once())->method('build')->with('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')
            ->willReturn(new VendorFinanceRuntimeProjection(
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
            ->willReturn(new VendorStatementDeliveryRuntimeProjection(
                tenantId: 'tenant-1',
                vendorId: '101',
                currency: 'USD',
                ownership: ['ownerUserId' => 5001],
                statement: [],
                export: null,
                recipients: [],
            ));
        $this->externalIntegration->expects(self::once())->method('build')->with('tenant-1', '101')
            ->willReturn(new VendorExternalIntegrationRuntimeProjection(
                tenantId: 'tenant-1',
                vendorId: '101',
                ownership: ['ownerUserId' => 5001],
                crm: [],
                webhooks: [],
                payoutBridge: [],
                surfaces: [],
            ));

        $payload = (new VendorRuntimeStatusProjectionBuilderService(
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
