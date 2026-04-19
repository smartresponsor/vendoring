<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ledger;

use App\Vendoring\Entity\Ledger\LedgerEntry;
use App\Vendoring\Service\Ledger\VendorSummaryService;
use App\Vendoring\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;

final class VendorSummaryServiceTest extends TestCase
{
    public function testBuildIncludesPayoutFeeInBalances(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'REVENUE', 'CASH', 200.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-10 10:00:00'));
        $repository->insert(new LedgerEntry('2', 'tenant-1', 'REFUNDS_PAYABLE', 'CASH', 35.0, 'USD', 'refund', 'ref-1', 'vendor-1', '2026-03-12 10:00:00'));
        $repository->insert(new LedgerEntry('3', 'tenant-1', 'payout_fee', 'CASH', 15.0, 'USD', 'fee', 'fee-1', 'vendor-1', '2026-03-15 10:00:00'));
        $repository->insert(new LedgerEntry('4', 'tenant-1', 'REVENUE', 'CASH', 999.0, 'EUR', 'invoice', 'inv-2', 'vendor-1', '2026-03-16 10:00:00'));

        $service = new VendorSummaryService($repository);
        $result = $service->build('tenant-1', 'vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD');

        self::assertSame('vendor-1', $result['vendorId']);
        self::assertSame(200.0, $result['balances']['REVENUE']);
        self::assertSame(35.0, $result['balances']['REFUNDS_PAYABLE']);
        self::assertSame(15.0, $result['balances']['payout_fee']);
        self::assertArrayHasKey('VENDOR_PAYABLE', $result['balances']);
        self::assertArrayHasKey('CASH', $result['balances']);
    }

    public function testBuildNormalizesDatetimeInputsToCalendarDateBoundaries(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'REVENUE', 'CASH', 40.0, 'USD', 'invoice', 'inv-early', 'vendor-1', '2026-03-01 00:00:00'));
        $repository->insert(new LedgerEntry('2', 'tenant-1', 'REVENUE', 'CASH', 60.0, 'USD', 'invoice', 'inv-late', 'vendor-1', '2026-03-31 23:59:59'));

        $service = new VendorSummaryService($repository);
        $result = $service->build('tenant-1', 'vendor-1', '2026-03-01 12:00:00', '2026-03-31 12:00:00', 'USD');

        self::assertSame(100.0, $result['balances']['REVENUE']);
    }

    public function testBuildSupportsUnfilteredCurrencyWhenEmptyStringIsProvided(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'REVENUE', 'CASH', 40.0, 'USD', 'invoice', 'inv-usd', 'vendor-1', '2026-03-10 10:00:00'));
        $repository->insert(new LedgerEntry('2', 'tenant-1', 'REVENUE', 'CASH', 60.0, 'EUR', 'invoice', 'inv-eur', 'vendor-1', '2026-03-11 10:00:00'));

        $service = new VendorSummaryService($repository);
        $result = $service->build('tenant-1', 'vendor-1', '2026-03-01', '2026-03-31', '');

        self::assertSame(100.0, $result['balances']['REVENUE']);
        self::assertSame('', $result['currency']);
    }
}
