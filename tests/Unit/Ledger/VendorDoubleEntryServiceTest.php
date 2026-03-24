<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ledger;

use App\DTO\Ledger\DoubleEntryDTO;
use App\Service\Ledger\VendorDoubleEntryService;
use App\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;

final class VendorDoubleEntryServiceTest extends TestCase
{
    public function testPostPersistsLedgerEntryWithProvidedTimestamp(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $service = new VendorDoubleEntryService($repository);
        $dto = new DoubleEntryDTO(
            'tenant-1',
            'VENDOR_PAYABLE',
            'REVENUE',
            125.50,
            'USD',
            'invoice',
            'inv-100',
            'vendor-1',
            '2026-03-15 08:30:00',
        );

        $result = $service->post($dto);

        self::assertCount(1, $result);
        self::assertSame($repository->all(), $result);
        self::assertSame('tenant-1', $result[0]->tenantId);
        self::assertSame('VENDOR_PAYABLE', $result[0]->debitAccount);
        self::assertSame('REVENUE', $result[0]->creditAccount);
        self::assertSame(125.50, $result[0]->amount);
        self::assertSame('USD', $result[0]->currency);
        self::assertSame('invoice', $result[0]->referenceType);
        self::assertSame('inv-100', $result[0]->referenceId);
        self::assertSame('vendor-1', $result[0]->vendorId);
        self::assertSame('2026-03-15 08:30:00', $result[0]->createdAt);
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $result[0]->id);
    }

    public function testPostCreatesTimestampWhenDtoDoesNotProvideOne(): void
    {
        $repository = new InMemoryLedgerEntryRepository();
        $service = new VendorDoubleEntryService($repository);
        $dto = new DoubleEntryDTO('tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 10.0, 'USD', 'invoice', 'inv-101');

        $result = $service->post($dto);

        self::assertCount(1, $result);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result[0]->createdAt);
    }
}
