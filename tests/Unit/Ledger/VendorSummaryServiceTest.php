<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ledger;

use App\Vendoring\Entity\Vendor\VendorLedgerEntity;
use App\Vendoring\Service\Ledger\VendorSummaryService;
use App\Vendoring\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;

final class VendorSummaryServiceTest extends TestCase
{
    public function testBuildIncludesPayoutFeeInBalances(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-1', 'REVENUE', 'CASH', 200.0, 'USD', '2026-03-10 10:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-1', 'refund', 'ref-1', 'REFUNDS_PAYABLE', 'CASH', 35.0, 'USD', '2026-03-12 10:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-1', 'fee', 'fee-1', 'payout_fee', 'CASH', 15.0, 'USD', '2026-03-15 10:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-2', 'REVENUE', 'CASH', 999.0, 'EUR', '2026-03-16 10:00:00'));

        $service = new VendorSummaryService($repository);
        $result = $service->build('vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD');

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
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-early', 'REVENUE', 'CASH', 40.0, 'USD', '2026-03-01 00:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-late', 'REVENUE', 'CASH', 60.0, 'USD', '2026-03-31 23:59:59'));

        $service = new VendorSummaryService($repository);
        $result = $service->build('vendor-1', '2026-03-01 12:00:00', '2026-03-31 12:00:00', 'USD');

        self::assertSame(100.0, $result['balances']['REVENUE']);
    }

    public function testBuildSupportsUnfilteredCurrencyWhenEmptyStringIsProvided(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-usd', 'REVENUE', 'CASH', 40.0, 'USD', '2026-03-10 10:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-eur', 'REVENUE', 'CASH', 60.0, 'EUR', '2026-03-11 10:00:00'));

        $service = new VendorSummaryService($repository);
        $result = $service->build('vendor-1', '2026-03-01', '2026-03-31', '');

        self::assertSame(100.0, $result['balances']['REVENUE']);
        self::assertSame('', $result['currency']);
    }
}
