<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Payout;

use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntryEntity;
use App\Vendoring\Service\Observability\VendorCorrelationContextService;
use App\Vendoring\Service\Observability\VendorMetricEmitterService;
use App\Vendoring\Service\Observability\VendorRuntimeLoggerService;
use App\Vendoring\Service\Ledger\VendorLedgerService;
use App\Vendoring\Service\Payout\VendorPayoutService;
use App\Vendoring\Tests\Support\Payout\InMemoryPayoutRepository;
use App\Vendoring\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

final class VendorPayoutServiceTest extends TestCase
{
    public function testCreateReturnsNullWhenVendorBalanceIsBelowThreshold(): void
    {
        $payoutRepository = new InMemoryPayoutRepository();
        $ledgerRepository = new InMemoryLedgerEntryRepository();
        $ledgerService = new VendorLedgerService($ledgerRepository);
        $metrics = new VendorMetricEmitterService();
        $service = new VendorPayoutService($payoutRepository, $ledgerRepository, $ledgerService, $metrics, $this->runtimeLogger());
        $ledgerRepository->insert(new VendorLedgerEntryEntity('1', 'tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 5.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-10 10:00:00'));

        $result = $service->create(new VendorCreatePayoutDTO('vendor-1', 'USD', 1000, 0.05));

        self::assertNull($result);
        self::assertSame([], $payoutRepository->all());
        self::assertSame([], $metrics->snapshot());
    }

    public function testCreateAndProcessPersistPayoutAndLedgerFlow(): void
    {
        $payoutRepository = new InMemoryPayoutRepository();
        $ledgerRepository = new InMemoryLedgerEntryRepository();
        $ledgerService = new VendorLedgerService($ledgerRepository);
        $metrics = new VendorMetricEmitterService();
        $service = new VendorPayoutService($payoutRepository, $ledgerRepository, $ledgerService, $metrics, $this->runtimeLogger());
        $ledgerRepository->insert(new VendorLedgerEntryEntity('seed-1', 'tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 15.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-10 10:00:00'));

        $payoutId = $service->create(new VendorCreatePayoutDTO('vendor-1', 'USD', 1000, 0.10));

        self::assertNotNull($payoutId);
        $payout = $payoutRepository->byId($payoutId);
        self::assertNotNull($payout);
        self::assertSame(1500, $payout->grossCents);
        self::assertSame(150, $payout->feeCents);
        self::assertSame(1350, $payout->netCents);
        self::assertSame('pending', $payout->status);

        $entriesAfterCreate = $ledgerRepository->all();
        self::assertCount(2, $entriesAfterCreate);
        self::assertSame('payout_reserve', $entriesAfterCreate[1]->debitAccount);
        self::assertSame('VENDOR_PAYABLE', $entriesAfterCreate[1]->creditAccount);
        self::assertSame(13.5, $entriesAfterCreate[1]->amount);

        self::assertTrue($service->process($payoutId));

        $processedPayout = $payoutRepository->byId($payoutId);
        self::assertNotNull($processedPayout);
        self::assertSame('processed', $processedPayout->status);
        self::assertNotNull($processedPayout->processedAt);

        $entriesAfterProcess = $ledgerRepository->all();
        self::assertCount(4, $entriesAfterProcess);
        self::assertSame('payout_processed', $entriesAfterProcess[2]->debitAccount);
        self::assertSame(13.5, $entriesAfterProcess[2]->amount);
        self::assertSame('payout_fee', $entriesAfterProcess[3]->debitAccount);
        self::assertSame(1.5, $entriesAfterProcess[3]->amount);

        self::assertSame(
            [
                ['name' => 'payout_created_total', 'tags' => ['currency' => 'USD']],
                ['name' => 'payout_processed_total', 'tags' => ['currency' => 'USD']],
            ],
            $metrics->snapshot(),
        );
    }

    private function runtimeLogger(): VendorRuntimeLoggerService
    {
        return new VendorRuntimeLoggerService(new VendorCorrelationContextService(), new RequestStack());
    }
}
