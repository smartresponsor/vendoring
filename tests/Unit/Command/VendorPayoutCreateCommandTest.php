<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\VendorPayoutCreateCommand;
use App\Entity\Ledger\LedgerEntry;
use App\Observability\Service\CorrelationContext;
use App\Observability\Service\MetricEmitter;
use App\Observability\Service\RuntimeLogger;
use App\Service\Ledger\VendorLedgerService;
use App\Service\Payout\VendorPayoutRequestService;
use App\Service\Payout\VendorPayoutService;
use App\Tests\Support\Payout\InMemoryPayoutRepository;
use App\Tests\Support\Repository\InMemoryLedgerEntryRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\RequestStack;

final class VendorPayoutCreateCommandTest extends TestCase
{
    public function testExecuteCreatesPayoutAndPrintsTextSurface(): void
    {
        $payoutRepository = new InMemoryPayoutRepository();
        $ledgerRepository = new InMemoryLedgerEntryRepository();
        $ledgerService = new VendorLedgerService($ledgerRepository);
        $metrics = new MetricEmitter();

        $ledgerRepository->insert(new LedgerEntry('seed-1', 'tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 25.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-20 10:00:00'));

        $command = new VendorPayoutCreateCommand(
            new VendorPayoutRequestService(),
            new VendorPayoutService($payoutRepository, $ledgerRepository, $ledgerService, $metrics, $this->runtimeLogger()),
            $payoutRepository,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--tenantId' => 'tenant-1',
            '--vendorId' => 'vendor-1',
            '--currency' => 'USD',
            '--thresholdCents' => '1000',
            '--retentionFeePercent' => '0.1',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('PAYOUT_CREATED', $tester->getDisplay());
        self::assertStringContainsString('vendorId=vendor-1', $tester->getDisplay());
        self::assertStringContainsString('grossCents=2500', $tester->getDisplay());
    }

    public function testExecutePrintsNoPayoutWhenBalanceIsBelowThreshold(): void
    {
        $payoutRepository = new InMemoryPayoutRepository();
        $ledgerRepository = new InMemoryLedgerEntryRepository();
        $ledgerService = new VendorLedgerService($ledgerRepository);
        $metrics = new MetricEmitter();

        $ledgerRepository->insert(new LedgerEntry('seed-1', 'tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 2.5, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-20 10:00:00'));

        $command = new VendorPayoutCreateCommand(
            new VendorPayoutRequestService(),
            new VendorPayoutService($payoutRepository, $ledgerRepository, $ledgerService, $metrics, $this->runtimeLogger()),
            $payoutRepository,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--tenantId' => 'tenant-1',
            '--vendorId' => 'vendor-1',
            '--currency' => 'USD',
            '--thresholdCents' => '1000',
            '--retentionFeePercent' => '0.1',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('NO_PAYOUT', $tester->getDisplay());
    }

    public function testExecuteReturnsFailureForMissingVendorId(): void
    {
        $payoutRepository = new InMemoryPayoutRepository();
        $ledgerRepository = new InMemoryLedgerEntryRepository();
        $ledgerService = new VendorLedgerService($ledgerRepository);
        $metrics = new MetricEmitter();

        $command = new VendorPayoutCreateCommand(
            new VendorPayoutRequestService(),
            new VendorPayoutService($payoutRepository, $ledgerRepository, $ledgerService, $metrics, $this->runtimeLogger()),
            $payoutRepository,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--tenantId' => 'tenant-1',
            '--currency' => 'USD',
            '--thresholdCents' => '1000',
            '--retentionFeePercent' => '0.1',
        ]);

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('vendorId required', $tester->getDisplay());
    }

    public function testExecutePrintsJsonSurfaceWhenRequested(): void
    {
        $payoutRepository = new InMemoryPayoutRepository();
        $ledgerRepository = new InMemoryLedgerEntryRepository();
        $ledgerService = new VendorLedgerService($ledgerRepository);
        $metrics = new MetricEmitter();

        $ledgerRepository->insert(new LedgerEntry('seed-1', 'tenant-1', 'VENDOR_PAYABLE', 'REVENUE', 20.0, 'USD', 'invoice', 'inv-1', 'vendor-1', '2026-03-20 10:00:00'));

        $command = new VendorPayoutCreateCommand(
            new VendorPayoutRequestService(),
            new VendorPayoutService($payoutRepository, $ledgerRepository, $ledgerService, $metrics, $this->runtimeLogger()),
            $payoutRepository,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--tenantId' => 'tenant-1',
            '--vendorId' => 'vendor-1',
            '--currency' => 'USD',
            '--thresholdCents' => '1000',
            '--retentionFeePercent' => '0.1',
            '--format' => 'json',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('"vendorId": "vendor-1"', $tester->getDisplay());
        self::assertStringContainsString('"currency": "USD"', $tester->getDisplay());
        self::assertStringContainsString('"status": "pending"', $tester->getDisplay());
    }

    private function runtimeLogger(): RuntimeLogger
    {
        return new RuntimeLogger(new CorrelationContext(), new RequestStack());
    }
}
