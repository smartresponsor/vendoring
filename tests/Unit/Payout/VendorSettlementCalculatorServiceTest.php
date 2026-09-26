<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Payout;

use App\Vendoring\Entity\Vendor\VendorLedgerEntity;
use App\Vendoring\Service\Payout\VendorSettlementCalculatorService;
use App\Vendoring\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;

final class VendorSettlementCalculatorServiceTest extends TestCase
{
    public function testNetForPeriodReturnsPositiveVendorPayableBalance(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-1', 'VENDOR_PAYABLE', 'REVENUE', 150.00, 'USD', '2026-03-10 10:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-1', 'payout', 'po-1', 'CASH', 'VENDOR_PAYABLE', 40.00, 'USD', '2026-03-11 10:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-1', 'invoice', 'inv-2', 'VENDOR_PAYABLE', 'REVENUE', 999.00, 'EUR', '2026-03-12 10:00:00'));
        $repository->insert(new VendorLedgerEntity('vendor-2', 'invoice', 'inv-3', 'VENDOR_PAYABLE', 'REVENUE', 777.00, 'USD', '2026-03-12 10:00:00'));

        $calculator = new VendorSettlementCalculatorService($repository);

        self::assertSame(110.0, $calculator->netForPeriod('vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
    }

    public function testNetForPeriodClampsNegativeBalanceToZero(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new VendorLedgerEntity('vendor-1', 'payout', 'po-1', 'CASH', 'VENDOR_PAYABLE', 75.00, 'USD', '2026-03-10 10:00:00'));

        $calculator = new VendorSettlementCalculatorService($repository);

        self::assertSame(0.0, $calculator->netForPeriod('vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD'));
    }
}
