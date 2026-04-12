<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\VendorPayoutProcessCommand;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class VendorPayoutProcessCommandTest extends TestCase
{
    private VendorPayoutServiceInterface&MockObject $payouts;

    protected function setUp(): void
    {
        $this->payouts = $this->createMock(VendorPayoutServiceInterface::class);
    }

    public function testExecuteShowsSuccessWhenPayoutWasProcessed(): void
    {
        $this->payouts->expects(self::once())->method('process')->with('payout-1')->willReturn(true);

        $tester = new CommandTester(new VendorPayoutProcessCommand($this->payouts));
        $exitCode = $tester->execute([
            'payoutId' => 'payout-1',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Processed payout payout-1.', $tester->getDisplay());
    }

    public function testExecuteShowsWarningWhenPayoutWasNotProcessed(): void
    {
        $this->payouts->expects(self::once())->method('process')->with('payout-1')->willReturn(false);

        $tester = new CommandTester(new VendorPayoutProcessCommand($this->payouts));
        $exitCode = $tester->execute([
            'payoutId' => 'payout-1',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Payout payout-1 was not processed.', $tester->getDisplay());
    }

    public function testExecuteCanEmitJsonOutput(): void
    {
        $this->payouts->expects(self::once())->method('process')->with('payout-1')->willReturn(false);

        $tester = new CommandTester(new VendorPayoutProcessCommand($this->payouts));
        $exitCode = $tester->execute([
            'payoutId' => 'payout-1',
            '--json' => true,
        ]);

        self::assertSame(0, $exitCode);
        self::assertJson($tester->getDisplay());

        $payload = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([
            'payoutId' => 'payout-1',
            'processed' => false,
            'status' => 'rejected',
        ], $payload);
    }
}
