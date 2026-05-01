<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Metric;

use App\Vendoring\DTO\Ledger\VendorLedgerAccountSumCriteriaDTO;
use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Metric\VendorMetricTrendRequestDTO;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerEntryRepositoryInterface;
use App\Vendoring\Service\Metric\VendorMetricService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorMetricServiceTest extends TestCase
{
    private VendorLedgerEntryRepositoryInterface&MockObject $ledger;

    protected function setUp(): void
    {
        $this->ledger = $this->createMock(VendorLedgerEntryRepositoryInterface::class);
    }

    public function testOverviewBuildsRevenueRefundPayoutAndBalanceFromLedgerAccounts(): void
    {
        $this->ledger
            ->expects(self::exactly(3))
            ->method('sumByAccount')
            ->willReturnCallback(static function (VendorLedgerAccountSumCriteriaDTO $criteria): float {
                return match ($criteria->accountCode) {
                    'REVENUE' => 120.0,
                    'REFUNDS_PAYABLE' => 15.0,
                    'VENDOR_PAYABLE' => 30.0,
                    default => 0.0,
                };
            });

        $payload = (new VendorMetricService($this->ledger))
            ->overview(new VendorMetricOverviewRequestDTO('tenant-1', 'vendor-1', '2026-03-01', '2026-03-31', 'USD'));

        self::assertSame([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'revenue' => 120.0,
            'refunds' => 15.0,
            'payouts' => 30.0,
            'balance' => 75.0,
        ], $payload);
    }

    public function testOverviewClampsNegativeLedgerSumsToZero(): void
    {
        $this->ledger
            ->expects(self::exactly(3))
            ->method('sumByAccount')
            ->willReturnCallback(static fn(): float => -100.0);

        $payload = (new VendorMetricService($this->ledger))
            ->overview(new VendorMetricOverviewRequestDTO('tenant-1', 'vendor-1'));

        self::assertSame(0.0, $payload['revenue']);
        self::assertSame(0.0, $payload['refunds']);
        self::assertSame(0.0, $payload['payouts']);
        self::assertSame(0.0, $payload['balance']);
    }

    public function testTrendsWrapsOverviewIntoSingleBucketPayload(): void
    {
        $this->ledger
            ->expects(self::exactly(3))
            ->method('sumByAccount')
            ->willReturnCallback(static function (VendorLedgerAccountSumCriteriaDTO $criteria): float {
                return match ($criteria->accountCode) {
                    'REVENUE' => 200.0,
                    'REFUNDS_PAYABLE' => 50.0,
                    'VENDOR_PAYABLE' => 25.0,
                    default => 0.0,
                };
            });

        $payload = (new VendorMetricService($this->ledger))
            ->trends(new VendorMetricTrendRequestDTO('tenant-1', 'vendor-1', '2026-01-01', '2026-03-31', 'quarter', 'EUR'));

        self::assertSame([
            [
                'tenantId' => 'tenant-1',
                'vendorId' => 'vendor-1',
                'from' => '2026-01-01',
                'to' => '2026-03-31',
                'currency' => 'EUR',
                'bucket' => 'quarter',
                'period' => '2026-01-01..2026-03-31',
                'revenue' => 200.0,
                'refunds' => 50.0,
                'payouts' => 25.0,
                'balance' => 125.0,
            ],
        ], $payload);
    }
}
