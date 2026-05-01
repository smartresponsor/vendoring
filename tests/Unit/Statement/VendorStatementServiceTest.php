<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntryEntity;
use App\Vendoring\Service\Statement\VendorStatementService;
use App\Vendoring\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;

final class VendorStatementServiceTest extends TestCase
{
    public function testBuildAggregatesEarningsRefundsAndClosingBalance(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new VendorLedgerEntryEntity('1', 'tenant-1', 'REVENUE', 'CASH', 200.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-10 10:00:00'));
        $repository->insert(new VendorLedgerEntryEntity('2', 'tenant-1', 'REFUNDS_PAYABLE', 'CASH', 35.0, 'USD', 'refund', 'ref-1', 'vendor-1', '2026-03-12 10:00:00'));
        $repository->insert(new VendorLedgerEntryEntity('3', 'tenant-1', 'REVENUE', 'CASH', 999.0, 'EUR', 'invoice', 'inv-2', 'vendor-1', '2026-03-13 10:00:00'));

        $service = new VendorStatementService($repository);
        $dto = new VendorStatementRequestDTO('tenant-1', 'vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD');

        $result = $service->build($dto);

        self::assertSame(0.0, $result['opening']);
        self::assertSame(200.0, $result['earnings']);
        self::assertSame(35.0, $result['refunds']);
        self::assertSame(0.0, $result['fees']);
        self::assertSame(165.0, $result['closing']);
        self::assertCount(3, $result['items']);
    }

    public function testExportCsvWritesStatementRows(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $repository->insert(new VendorLedgerEntryEntity('1', 'tenant-1', 'REVENUE', 'CASH', 120.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-10 10:00:00'));

        $service = new VendorStatementService($repository);
        $dto = new VendorStatementRequestDTO('tenant-1', 'vendor-1', '2026-03-01 00:00:00', '2026-03-31 23:59:59', 'USD');

        $path = $service->exportCsv($dto);
        $csv = file_get_contents($path);
        if (is_file($path)) {
            unlink($path);
        }

        self::assertNotFalse($csv);
        self::assertStringContainsString('Section,Amount,Currency', $csv);
        self::assertStringContainsString('earnings,120,USD', $csv);
        self::assertStringContainsString('Closing,120,USD', $csv);
    }
}
