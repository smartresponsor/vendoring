<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use App\Vendoring\Projection\Vendor\VendorOwnershipView;
use App\Vendoring\Service\Finance\VendorFinanceRuntimeViewBuilderService;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutAccountRepositoryInterface;
use App\Vendoring\ServiceInterface\Metric\VendorMetricServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipViewBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorFinanceRuntimeViewBuilderTest extends TestCase
{
    private VendorOwnershipViewBuilderServiceInterface&MockObject $ownership;
    private VendorMetricServiceInterface&MockObject $metrics;
    private VendorPayoutAccountRepositoryInterface&MockObject $accounts;
    private VendorStatementServiceInterface&MockObject $statements;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipViewBuilderServiceInterface::class);
        $this->metrics = $this->createMock(VendorMetricServiceInterface::class);
        $this->accounts = $this->createMock(VendorPayoutAccountRepositoryInterface::class);
        $this->statements = $this->createMock(VendorStatementServiceInterface::class);
    }

    public function testBuildIncludesOwnershipMetricsPayoutAccountAndStatementWhenPeriodIsPresent(): void
    {
        $metricOverview = [
            'tenantId' => 'tenant-1',
            'vendorId' => '101',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'revenue' => 100.0,
            'refunds' => 10.0,
            'payouts' => 30.0,
            'balance' => 60.0,
        ];
        $statement = [
            'tenantId' => 'tenant-1',
            'vendorId' => '101',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'opening' => 0.0,
            'earnings' => 100.0,
            'refunds' => 10.0,
            'fees' => 5.0,
            'closing' => 85.0,
            'items' => [],
        ];

        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, [['userId' => 5002, 'role' => 'manager', 'status' => 'active', 'isPrimary' => false, 'grantedAt' => '2026-03-01T00:00:00+00:00', 'revokedAt' => null, 'capabilities' => []]]));
        $this->metrics->expects(self::once())->method('overview')->with(self::callback(function (VendorMetricOverviewRequestDTO $request): bool {
            self::assertSame('tenant-1', $request->tenantId);
            self::assertSame('101', $request->vendorId);
            self::assertSame('2026-03-01', $request->from);
            self::assertSame('2026-03-31', $request->to);
            self::assertSame('USD', $request->currency);

            return true;
        }))
            ->willReturn($metricOverview);
        $this->accounts->expects(self::once())->method('get')->with('tenant-1', '101')
            ->willReturn(new VendorPayoutAccountEntity('acc-1', 'tenant-1', '101', 'bank', 'iban-123', 'USD', true, '2026-03-01 10:00:00'));
        $this->statements->expects(self::once())->method('build')->with(self::callback(function (VendorStatementRequestDTO $dto): bool {
            self::assertSame('tenant-1', $dto->tenantId);
            self::assertSame('101', $dto->vendorId);
            self::assertSame('2026-03-01', $dto->from);
            self::assertSame('2026-03-31', $dto->to);
            self::assertSame('USD', $dto->currency);

            return true;
        }))->willReturn($statement);

        $view = (new VendorFinanceRuntimeViewBuilderService(
            $this->ownership,
            $this->metrics,
            $this->accounts,
            $this->statements,
        ))->build('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD')->toArray();

        self::assertSame('tenant-1', $view['tenantId']);
        self::assertSame('101', $view['vendorId']);
        self::assertSame('USD', $view['currency']);
        self::assertIsArray($view['ownership']);
        self::assertSame(5001, $view['ownership']['ownerUserId']);
        self::assertSame($metricOverview, $view['metricOverview']);
        self::assertIsArray($view['payoutAccount']);
        self::assertSame('bank', $view['payoutAccount']['provider']);
        self::assertSame('iban-123', $view['payoutAccount']['accountRef']);
        self::assertSame($statement, $view['statement']);
    }

    public function testBuildSkipsOwnershipAndStatementForNonNumericVendorIdWithoutPeriod(): void
    {
        $metricOverview = [
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-alpha',
            'from' => null,
            'to' => null,
            'currency' => 'EUR',
            'revenue' => 0.0,
            'refunds' => 0.0,
            'payouts' => 0.0,
            'balance' => 0.0,
        ];

        $this->ownership->expects(self::never())->method('buildForVendorId');
        $this->metrics->expects(self::once())->method('overview')->with(self::callback(function (VendorMetricOverviewRequestDTO $request): bool {
            self::assertSame('tenant-1', $request->tenantId);
            self::assertSame('vendor-alpha', $request->vendorId);
            self::assertNull($request->from);
            self::assertNull($request->to);
            self::assertSame('EUR', $request->currency);

            return true;
        }))
            ->willReturn($metricOverview);
        $this->accounts->expects(self::once())->method('get')->with('tenant-1', 'vendor-alpha')->willReturn(null);
        $this->statements->expects(self::never())->method('build');

        $view = (new VendorFinanceRuntimeViewBuilderService(
            $this->ownership,
            $this->metrics,
            $this->accounts,
            $this->statements,
        ))->build('tenant-1', 'vendor-alpha', null, null, 'EUR')->toArray();

        self::assertNull($view['ownership']);
        self::assertSame($metricOverview, $view['metricOverview']);
        self::assertNull($view['payoutAccount']);
        self::assertNull($view['statement']);
        self::assertSame('EUR', $view['currency']);
    }
}
