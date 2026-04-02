<?php

declare(strict_types=1);

namespace App\Tests\Unit\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\Entity\Ledger\LedgerEntry;
use App\Service\Statement\VendorStatementService;
use App\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;

final class VendorStatementServiceTest extends TestCase
{
    public function testBuildAggregatesOpeningEarningsRefundsFeesAndClosingBalance(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'REVENUE', 'CASH', 100.0, 'USD', 'invoice', 'inv-0', 'vendor-1', '2026-02-20 10:00:00'));
        $repository->insert(new LedgerEntry('2', 'tenant-1', 'REFUNDS_PAYABLE', 'CASH', 10.0, 'USD', 'refund', 'ref-0', 'vendor-1', '2026-02-22 10:00:00'));
        $repository->insert(new LedgerEntry('3', 'tenant-1', 'payout_fee', 'CASH', 5.0, 'USD', 'fee', 'fee-0', 'vendor-1', '2026-02-25 10:00:00'));
        $repository->insert(new LedgerEntry('4', 'tenant-1', 'REVENUE', 'CASH', 200.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-10 10:00:00'));
        $repository->insert(new LedgerEntry('5', 'tenant-1', 'REFUNDS_PAYABLE', 'CASH', 35.0, 'USD', 'refund', 'ref-1', 'vendor-1', '2026-03-12 10:00:00'));
        $repository->insert(new LedgerEntry('6', 'tenant-1', 'payout_fee', 'CASH', 15.0, 'USD', 'fee', 'fee-1', 'vendor-1', '2026-03-15 10:00:00'));
        $repository->insert(new LedgerEntry('7', 'tenant-1', 'REVENUE', 'CASH', 999.0, 'EUR', 'invoice', 'inv-2', 'vendor-1', '2026-03-13 10:00:00'));

        $service = new VendorStatementService($repository);
        $dto = new VendorStatementRequestDTO('tenant-1', 'vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD');

        $result = $service->build($dto);

        self::assertSame(85.0, $result['opening']);
        self::assertSame(200.0, $result['earnings']);
        self::assertSame(35.0, $result['refunds']);
        self::assertSame(15.0, $result['fees']);
        self::assertSame(235.0, $result['closing']);
        self::assertCount(3, $result['items']);
    }

    public function testBuildNormalizesDateTimeInputsToCalendarDateBoundaries(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'REVENUE', 'CASH', 40.0, 'USD', 'invoice', 'inv-early', 'vendor-1', '2026-03-01 00:00:00'));
        $repository->insert(new LedgerEntry('2', 'tenant-1', 'REVENUE', 'CASH', 60.0, 'USD', 'invoice', 'inv-late', 'vendor-1', '2026-03-31 23:59:59'));

        $service = new VendorStatementService($repository);
        $dto = new VendorStatementRequestDTO('tenant-1', 'vendor-1', '2026-03-01 12:00:00', '2026-03-31 12:00:00', 'USD');

        $result = $service->build($dto);

        self::assertSame(100.0, $result['earnings']);
        self::assertSame(100.0, $result['closing']);
    }

    public function testExportCsvWritesStatementRows(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new LedgerEntry('1', 'tenant-1', 'REVENUE', 'CASH', 120.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-02-10 10:00:00'));
        $repository->insert(new LedgerEntry('2', 'tenant-1', 'payout_fee', 'CASH', 20.0, 'USD', 'fee', 'fee-1', 'vendor-1', '2026-03-11 10:00:00'));

        $service = new VendorStatementService($repository);
        $dto = new VendorStatementRequestDTO('tenant-1', 'vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD');

        $path = $service->exportCsv($dto);
        $csv = file_get_contents($path);
        if (is_file($path)) {
            unlink($path);
        }

        self::assertNotFalse($csv);
        self::assertStringContainsString('Section,Amount,Currency', $csv);
        self::assertStringContainsString('fees,20,USD', $csv);
        self::assertStringContainsString('Opening,120,USD', $csv);
        self::assertStringContainsString('Closing,100,USD', $csv);
    }
}
