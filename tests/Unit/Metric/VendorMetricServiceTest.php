<?php

declare(strict_types=1);

namespace App\Tests\Unit\Metric;

use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\Service\Metric\VendorMetricService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorMetricServiceTest extends TestCase
{
    private LedgerEntryRepositoryInterface&MockObject $ledger;

    protected function setUp(): void
    {
        $this->ledger = $this->createMock(LedgerEntryRepositoryInterface::class);
    }

    public function testOverviewBuildsRevenueRefundPayoutAndBalanceFromLedgerAccounts(): void
    {
        $this->ledger
            ->expects(self::exactly(3))
            ->method('sumByAccount')
            ->willReturnMap([
                ['tenant-1', 'REVENUE', '2026-03-01', '2026-03-31', 'vendor-1', 'USD', 120.0],
                ['tenant-1', 'REFUNDS_PAYABLE', '2026-03-01', '2026-03-31', 'vendor-1', 'USD', 15.0],
                ['tenant-1', 'VENDOR_PAYABLE', '2026-03-01', '2026-03-31', 'vendor-1', 'USD', 30.0],
            ]);

        $payload = (new VendorMetricService($this->ledger))
            ->overview('tenant-1', 'vendor-1', '2026-03-01', '2026-03-31', 'USD');

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
            ->willReturnMap([
                ['tenant-1', 'REVENUE', null, null, 'vendor-1', 'USD', -100.0],
                ['tenant-1', 'REFUNDS_PAYABLE', null, null, 'vendor-1', 'USD', -5.0],
                ['tenant-1', 'VENDOR_PAYABLE', null, null, 'vendor-1', 'USD', -8.0],
            ]);

        $payload = (new VendorMetricService($this->ledger))
            ->overview('tenant-1', 'vendor-1');

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
            ->willReturnMap([
                ['tenant-1', 'REVENUE', '2026-01-01', '2026-03-31', 'vendor-1', 'EUR', 200.0],
                ['tenant-1', 'REFUNDS_PAYABLE', '2026-01-01', '2026-03-31', 'vendor-1', 'EUR', 50.0],
                ['tenant-1', 'VENDOR_PAYABLE', '2026-01-01', '2026-03-31', 'vendor-1', 'EUR', 25.0],
            ]);

        $payload = (new VendorMetricService($this->ledger))
            ->trends('tenant-1', 'vendor-1', '2026-01-01', '2026-03-31', 'quarter', 'EUR');

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
