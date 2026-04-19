<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Payout;

use App\Vendoring\Entity\Ledger\LedgerEntry;
use App\Vendoring\Service\Payout\VendorSettlementCalculatorService;
use App\Vendoring\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;

final class VendorSettlementCalculatorServiceTest extends TestCase
{
    public function testNetForPeriodReturnsPositiveVendorPayableBalance(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 150.00, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-10 10:00:00'));
        $repository->insert(new LedgerEntry('2', 'tenant-1', 'CASH', 'VENDOR_PAYABLE', 40.00, 'USD', 'payout', 'po-1', 'vendor-1', '2026-03-11 10:00:00'));
        $repository->insert(new LedgerEntry('3', 'tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 999.00, 'EUR', 'invoice', 'inv-2', 'vendor-1', '2026-03-12 10:00:00'));
        $repository->insert(new LedgerEntry('4', 'tenant-2', 'VENDOR_PAYABLE', 'REVENUE', 777.00, 'USD', 'invoice', 'inv-3', 'vendor-1', '2026-03-12 10:00:00'));

        $calculator = new VendorSettlementCalculatorService($repository);

        self::assertSame(110.0, $calculator->netForPeriod('tenant-1', 'vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
    }

    public function testNetForPeriodClampsNegativeBalanceToZero(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'CASH', 'VENDOR_PAYABLE', 75.00, 'USD', 'payout', 'po-1', 'vendor-1', '2026-03-10 10:00:00'));

        $calculator = new VendorSettlementCalculatorService($repository);

        self::assertSame(0.0, $calculator->netForPeriod('tenant-1', 'vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
    }
}
