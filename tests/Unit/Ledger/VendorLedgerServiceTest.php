<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Ledger;

use App\Vendoring\DTO\Ledger\VendorLedgerEntryDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntryEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerEntryRepositoryInterface;
use App\Vendoring\Service\Ledger\VendorLedgerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorLedgerServiceTest extends TestCase
{
    private VendorLedgerEntryRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendorLedgerEntryRepositoryInterface::class);
    }

    public function testRecordCreatesDebitSideEntryAndPersistsIt(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('insert')
            ->with(self::callback(function (VendorLedgerEntryEntity $entry): bool {
                self::assertSame('tenant-1', $entry->tenantId);
                self::assertSame('order_paid', $entry->debitAccount);
                self::assertSame('VENDOR_PAYABLE', $entry->creditAccount);
                self::assertSame(12.5, $entry->amount);
                self::assertSame('USD', $entry->currency);
                self::assertSame('order_paid', $entry->type);
                self::assertSame('entity-1', $entry->entityId);
                self::assertSame('vendor-1', $entry->vendorId);
                self::assertSame('2026-03-31 10:00:00', $entry->createdAt);

                return true;
            }));

        $entry = (new VendorLedgerService($this->repository))->record(new VendorLedgerEntryDTO(
            type: 'order_paid',
            entityId: 'entity-1',
            sagaId: 'saga-1',
            vendorId: 'vendor-1',
            amountCents: 1250,
            currency: 'USD',
            direction: 'debit',
            tenantId: 'tenant-1',
            occurredAt: '2026-03-31 10:00:00',
        ));

        self::assertSame('order_paid', $entry->debitAccount);
        self::assertSame('VENDOR_PAYABLE', $entry->creditAccount);
    }

    public function testRecordCreatesCreditSideEntry(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('insert')
            ->with(self::callback(function (VendorLedgerEntryEntity $entry): bool {
                self::assertSame('VENDOR_PAYABLE', $entry->debitAccount);
                self::assertSame('refund', $entry->creditAccount);
                self::assertSame(5.0, $entry->amount);

                return true;
            }));

        $entry = (new VendorLedgerService($this->repository))->record(new VendorLedgerEntryDTO(
            type: 'refund',
            entityId: 'entity-2',
            sagaId: 'saga-2',
            vendorId: 'vendor-1',
            amountCents: 500,
            currency: 'USD',
            direction: 'credit',
        ));

        self::assertSame('VENDOR_PAYABLE', $entry->debitAccount);
        self::assertSame('refund', $entry->creditAccount);
    }

    public function testRecordRejectsUnsupportedDirection(): void
    {
        $this->repository->expects(self::never())->method('insert');

        $service = new VendorLedgerService($this->repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported ledger direction "sideways".');

        $service->record(new VendorLedgerEntryDTO(
            type: 'refund',
            entityId: 'entity-2',
            sagaId: 'saga-2',
            vendorId: 'vendor-1',
            amountCents: 500,
            currency: 'USD',
            direction: 'sideways',
        ));
    }
}
