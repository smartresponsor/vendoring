<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\DTO\Statement\VendorStatementRequestDTO;
use App\Entity\Payout\PayoutAccount;
use App\Projection\VendorOwnershipView;
use App\Service\VendorFinanceRuntimeViewBuilder;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use App\ServiceInterface\Metric\VendorMetricServiceInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorFinanceRuntimeViewBuilderTest extends TestCase
{
    private VendorOwnershipViewBuilderInterface&MockObject $ownership;
    private VendorMetricServiceInterface&MockObject $metrics;
    private PayoutAccountRepositoryInterface&MockObject $accounts;
    private VendorStatementServiceInterface&MockObject $statements;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipViewBuilderInterface::class);
        $this->metrics = $this->createMock(VendorMetricServiceInterface::class);
        $this->accounts = $this->createMock(PayoutAccountRepositoryInterface::class);
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
            ->willReturn(new VendorOwnershipView(101, 5001, [['userId' => 5002, 'role' => 'manager']]));
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
            ->willReturn(new PayoutAccount('acc-1', 'tenant-1', '101', 'bank', 'iban-123', 'USD', true, '2026-03-01 10:00:00'));
        $this->statements->expects(self::once())->method('build')->with(self::callback(function (VendorStatementRequestDTO $dto): bool {
            self::assertSame('tenant-1', $dto->tenantId);
            self::assertSame('101', $dto->vendorId);
            self::assertSame('2026-03-01', $dto->from);
            self::assertSame('2026-03-31', $dto->to);
            self::assertSame('USD', $dto->currency);

            return true;
        }))->willReturn($statement);

        $view = (new VendorFinanceRuntimeViewBuilder(
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

        $view = (new VendorFinanceRuntimeViewBuilder(
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
