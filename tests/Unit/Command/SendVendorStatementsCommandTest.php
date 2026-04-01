<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\SendVendorStatementsCommand;
use App\DTO\Statement\VendorStatementRecipientDTO;
use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;
use App\ServiceInterface\Statement\VendorStatementMailerServiceInterface;
use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class SendVendorStatementsCommandTest extends TestCase
{
    private VendorStatementServiceInterface&MockObject $statements;
    private StatementExporterPDFInterface&MockObject $pdf;
    private VendorStatementMailerServiceInterface&MockObject $mailer;
    private VendorStatementRecipientProviderInterface&MockObject $recipients;

    protected function setUp(): void
    {
        $this->statements = $this->createMock(VendorStatementServiceInterface::class);
        $this->pdf = $this->createMock(StatementExporterPDFInterface::class);
        $this->mailer = $this->createMock(VendorStatementMailerServiceInterface::class);
        $this->recipients = $this->createMock(VendorStatementRecipientProviderInterface::class);
    }

    public function testExecuteUsesManualRecipientOptionsAndNormalizesTrimmedValues(): void
    {
        $statement = [
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'opening' => 0.0,
            'earnings' => 0.0,
            'refunds' => 0.0,
            'fees' => 0.0,
            'closing' => 0.0,
            'items' => [],
        ];

        $this->recipients->expects(self::never())->method('forPeriod');
        $this->statements
            ->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementRequestDTO $dto): bool {
                self::assertSame('tenant-1', $dto->tenantId);
                self::assertSame('vendor-1', $dto->vendorId);
                self::assertSame('2026-03-01', $dto->from);
                self::assertSame('2026-03-31', $dto->to);
                self::assertSame('USD', $dto->currency);

                return true;
            }))
            ->willReturn($statement);
        $this->pdf->expects(self::once())->method('export')->willReturn('/tmp/statement.pdf');
        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->with('tenant-1', 'vendor-1', 'vendor@example.com', '/tmp/statement.pdf', 'March 2026')
            ->willReturn([
                'ok' => true,
                'email' => 'vendor@example.com',
                'periodLabel' => 'March 2026',
                'pdfPath' => '/tmp/statement.pdf',
                'attached' => false,
                'message' => 'sent',
            ]);

        $tester = new CommandTester(new SendVendorStatementsCommand(
            $this->statements,
            $this->pdf,
            $this->mailer,
            $this->recipients,
        ));

        $exitCode = $tester->execute([
            '--tenant-id' => ' tenant-1 ',
            '--vendor-id' => ' vendor-1 ',
            '--email' => ' vendor@example.com ',
            '--currency' => ' usd ',
            '--from' => ' 2026-03-01 ',
            '--to' => ' 2026-03-31 ',
            '--period-label' => ' March 2026 ',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('[tenant-1/vendor-1] SENT email=vendor@example.com period=March 2026 currency=USD pdf=/tmp/statement.pdf attached=no message=sent', $tester->getDisplay());
    }

    public function testExecuteUsesRecipientProviderWhenManualRecipientOptionsAreAbsent(): void
    {
        $statement = [
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'EUR',
            'opening' => 0.0,
            'earnings' => 0.0,
            'refunds' => 0.0,
            'fees' => 0.0,
            'closing' => 0.0,
            'items' => [],
        ];

        $this->recipients
            ->expects(self::once())
            ->method('forPeriod')
            ->with('2026-03-01', '2026-03-31')
            ->willReturn([
                new VendorStatementRecipientDTO('tenant-1', 'vendor-1', 'vendor@example.com', 'EUR'),
            ]);
        $this->statements->expects(self::once())->method('build')->willReturn($statement);
        $this->pdf->expects(self::once())->method('export')->willReturn('/tmp/provider-statement.pdf');
        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->with('tenant-1', 'vendor-1', 'vendor@example.com', '/tmp/provider-statement.pdf', 'March 2026')
            ->willReturn([
                'ok' => false,
                'email' => 'vendor@example.com',
                'periodLabel' => 'March 2026',
                'pdfPath' => '/tmp/provider-statement.pdf',
                'attached' => true,
                'message' => 'statement_mail_send_failed',
            ]);

        $tester = new CommandTester(new SendVendorStatementsCommand(
            $this->statements,
            $this->pdf,
            $this->mailer,
            $this->recipients,
        ));

        $exitCode = $tester->execute([
            '--from' => '2026-03-01',
            '--to' => '2026-03-31',
            '--period-label' => 'March 2026',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('[tenant-1/vendor-1] FAIL email=vendor@example.com period=March 2026 currency=EUR pdf=/tmp/provider-statement.pdf attached=yes message=statement_mail_send_failed', $tester->getDisplay());
    }

    public function testExecutePrintsNoRecipientsWhenManualRecipientOptionsArePartial(): void
    {
        $this->recipients->expects(self::never())->method('forPeriod');
        $this->statements->expects(self::never())->method('build');
        $this->pdf->expects(self::never())->method('export');
        $this->mailer->expects(self::never())->method('send');

        $tester = new CommandTester(new SendVendorStatementsCommand(
            $this->statements,
            $this->pdf,
            $this->mailer,
            $this->recipients,
        ));

        $exitCode = $tester->execute([
            '--tenant-id' => 'tenant-1',
            '--from' => '2026-03-01',
            '--to' => '2026-03-31',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('NO_RECIPIENTS period=March 2026 from=2026-03-01 to=2026-03-31', $tester->getDisplay());
    }
}
