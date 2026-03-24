<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\SendVendorStatementsCommand;
use App\DTO\Statement\VendorStatementRecipientDTO;
use App\Tests\Support\Statement\FakeStatementExporterPDF;
use App\Tests\Support\Statement\FakeVendorStatementMailerService;
use App\Tests\Support\Statement\FakeVendorStatementRecipientProvider;
use App\Tests\Support\Statement\FakeVendorStatementService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class SendVendorStatementsCommandTest extends TestCase
{
    public function testExecuteWritesSentLineFromProviderRecipient(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'statement-command-');
        self::assertNotFalse($pdf);
        file_put_contents($pdf, 'pdf');

        $command = new SendVendorStatementsCommand(
            new FakeVendorStatementService(['balance' => 100.0]),
            new FakeStatementExporterPDF($pdf),
            new FakeVendorStatementMailerService(true, 'sent'),
            new FakeVendorStatementRecipientProvider([
                new VendorStatementRecipientDTO('tenant-a', 'vendor-a', 'vendor@example.com', 'USD'),
            ]),
        );

        $tester = new CommandTester($command);
        $status = $tester->execute(['--from' => '2026-03-01', '--to' => '2026-03-31']);

        self::assertSame(0, $status);
        self::assertStringContainsString('SENT', $tester->getDisplay());
        self::assertStringContainsString('vendor@example.com', $tester->getDisplay());

        unlink($pdf);
    }

    public function testExecuteWritesSentLineFromCliRecipient(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'statement-command-');
        self::assertNotFalse($pdf);
        file_put_contents($pdf, 'pdf');

        $command = new SendVendorStatementsCommand(
            new FakeVendorStatementService(['balance' => 100.0]),
            new FakeStatementExporterPDF($pdf),
            new FakeVendorStatementMailerService(true, 'sent'),
            new FakeVendorStatementRecipientProvider([]),
        );

        $tester = new CommandTester($command);
        $status = $tester->execute([
            '--tenant-id' => 'tenant-cli',
            '--vendor-id' => 'vendor-cli',
            '--email' => 'cli@example.com',
            '--currency' => 'EUR',
            '--from' => '2026-03-01',
            '--to' => '2026-03-31',
            '--period-label' => 'March 2026',
        ]);

        self::assertSame(0, $status);
        self::assertStringContainsString('SENT', $tester->getDisplay());
        self::assertStringContainsString('cli@example.com', $tester->getDisplay());
        self::assertStringContainsString('currency=EUR', $tester->getDisplay());

        unlink($pdf);
    }

    public function testExecuteWritesNoRecipientsLineWhenNothingResolved(): void
    {
        $command = new SendVendorStatementsCommand(
            new FakeVendorStatementService(['balance' => 0.0]),
            new FakeStatementExporterPDF(sys_get_temp_dir().'/missing-statement.pdf'),
            new FakeVendorStatementMailerService(true, 'sent'),
            new FakeVendorStatementRecipientProvider([]),
        );

        $tester = new CommandTester($command);
        $status = $tester->execute(['--from' => '2026-03-01', '--to' => '2026-03-31']);

        self::assertSame(0, $status);
        self::assertStringContainsString('NO_RECIPIENTS', $tester->getDisplay());
    }
}
