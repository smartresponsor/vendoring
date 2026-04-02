<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\VendorPayoutCreateCommand;
use App\DTO\Payout\CreatePayoutDTO;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class VendorPayoutCreateCommandTest extends TestCase
{
    private VendorPayoutServiceInterface&MockObject $payouts;

    protected function setUp(): void
    {
        $this->payouts = $this->createMock(VendorPayoutServiceInterface::class);
    }

    public function testExecuteCreatesPayoutAndNormalizesCurrency(): void
    {
        $this->payouts
            ->expects(self::once())
            ->method('create')
            ->with(self::callback(function (CreatePayoutDTO $dto): bool {
                self::assertSame('tenant-1', $dto->tenantId);
                self::assertSame('vendor-1', $dto->vendorId);
                self::assertSame('USD', $dto->currency);
                self::assertSame(1000, $dto->thresholdCents);
                self::assertSame(0.05, $dto->retentionFeePercent);

                return true;
            }))
            ->willReturn('payout-1');
        $this->payouts->expects(self::never())->method('process');

        $tester = new CommandTester(new VendorPayoutCreateCommand($this->payouts));
        $exitCode = $tester->execute([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'usd',
            'thresholdCents' => '1000',
            'retentionFeePercent' => '0.05',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Created payout payout-1.', $tester->getDisplay());
    }

    public function testExecuteCanProcessCreatedPayout(): void
    {
        $this->payouts->expects(self::once())->method('create')->willReturn('payout-1');
        $this->payouts->expects(self::once())->method('process')->with('payout-1')->willReturn(true);

        $tester = new CommandTester(new VendorPayoutCreateCommand($this->payouts));
        $exitCode = $tester->execute([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => '1000',
            'retentionFeePercent' => '0.05',
            '--process' => true,
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Created payout payout-1.', $tester->getDisplay());
        self::assertStringContainsString('Processed payout payout-1.', $tester->getDisplay());
    }

    public function testExecuteWarnsWhenThresholdWasNotReached(): void
    {
        $this->payouts->expects(self::once())->method('create')->willReturn(null);
        $this->payouts->expects(self::never())->method('process');

        $tester = new CommandTester(new VendorPayoutCreateCommand($this->payouts));
        $exitCode = $tester->execute([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => '1000',
            'retentionFeePercent' => '0.05',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('No payout created. Threshold was not reached.', $tester->getDisplay());
    }

    public function testExecuteCanEmitJsonOutput(): void
    {
        $this->payouts->expects(self::once())->method('create')->willReturn('payout-1');
        $this->payouts->expects(self::once())->method('process')->with('payout-1')->willReturn(false);

        $tester = new CommandTester(new VendorPayoutCreateCommand($this->payouts));
        $exitCode = $tester->execute([
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'currency' => 'USD',
            'thresholdCents' => '1000',
            'retentionFeePercent' => '0.05',
            '--process' => true,
            '--json' => true,
        ]);

        self::assertSame(0, $exitCode);
        self::assertJson($tester->getDisplay());

        $payload = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([
            'created' => true,
            'payoutId' => 'payout-1',
            'processed' => false,
        ], $payload);
    }
}
