<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Payout;

use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntity;
use App\Vendoring\Service\Ledger\VendorLedgerService;
use App\Vendoring\Service\Observability\VendorCorrelationContextService;
use App\Vendoring\Service\Observability\VendorMetricEmitterService;
use App\Vendoring\Service\Observability\VendorRuntimeLoggerService;
use App\Vendoring\Service\Payout\VendorPayoutService;
use App\Vendoring\Tests\Support\Payout\InMemoryPayoutRepository;
use App\Vendoring\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
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
        $ledgerRepository->insert(new VendorLedgerEntity('tenant-1', 'vendor-1', 'invoice', 'inv-1', 'VENDOR_PAYABLE', 'REVENUE', 5.0, 'USD', '2026-03-10 10:00:00'));

        $result = $service->create(new VendorCreatePayoutDTO('tenant-1', 'vendor-1', 'USD', 1000, 0.05));

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
        $ledgerRepository->insert(new VendorLedgerEntity('tenant-1', 'vendor-1', 'invoice', 'inv-1', 'VENDOR_PAYABLE', 'REVENUE', 15.0, 'USD', '2026-03-10 10:00:00'));

        $payoutId = $service->create(new VendorCreatePayoutDTO('tenant-1', 'vendor-1', 'USD', 1000, 0.10));

        self::assertNotNull($payoutId);
        $payout = $payoutRepository->byId('tenant-1', $payoutId);
        self::assertNotNull($payout);
        self::assertSame(1500, $payout->grossCents);
        self::assertSame(150, $payout->feeCents);
        self::assertSame(1350, $payout->netCents);
        self::assertSame('pending', $payout->status);

        $entriesAfterCreate = $ledgerRepository->all();
        self::assertCount(2, $entriesAfterCreate);
        self::assertSame('payout_reserve', $entriesAfterCreate[1]->debitAccount);
        self::assertSame('VENDOR_PAYABLE', $entriesAfterCreate[1]->creditAccount);
        self::assertSame(15.0, $entriesAfterCreate[1]->amount);
        self::assertSame(0, $ledgerRepository->balancesForVendor('tenant-1', 'vendor-1')[0]->balanceCents);
        self::assertNull($service->create(new VendorCreatePayoutDTO('tenant-1', 'vendor-1', 'USD', 1000, 0.10)));

        self::assertNull($payoutRepository->byId('tenant-2', $payoutId));
        self::assertFalse($service->process('tenant-2', $payoutId));
        try {
            $service->process('tenant-1', $payoutId);
            self::fail('Payout must not be marked processed without a real provider.');
        } catch (\App\Vendoring\Exception\Payout\VendorPayoutProviderNotConfiguredException $exception) {
            self::assertStringContainsString('not configured', $exception->getMessage());
        }

        $stillPending = $payoutRepository->byId('tenant-1', $payoutId);
        self::assertNotNull($stillPending);
        self::assertSame('pending', $stillPending->status);
        self::assertNull($stillPending->processedAt);
        self::assertCount(2, $ledgerRepository->all());
        self::assertSame(
            [['name' => 'payout_created_total', 'tags' => ['currency' => 'USD']]],
            $metrics->snapshot(),
        );
    }

    private function runtimeLogger(): VendorRuntimeLoggerService
    {
        return new VendorRuntimeLoggerService(
            new NullLogger(),
            new VendorCorrelationContextService(),
            new RequestStack(),
        );
    }
}
