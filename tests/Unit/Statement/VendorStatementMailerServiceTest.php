<?php

declare(strict_types=1);

namespace App\Tests\Unit\Statement;

use App\Observability\Service\MetricEmitter;
use App\Service\Statement\VendorStatementMailerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class VendorStatementMailerServiceTest extends TestCase
{
    private MailerInterface&MockObject $mailer;
    private MetricEmitter $metrics;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->metrics = new MetricEmitter();
    }

    public function testSendRejectsInvalidEmailWithoutCallingMailer(): void
    {
        $this->mailer->expects(self::never())->method('send');

        $service = new VendorStatementMailerService($this->mailer, $this->metrics);
        $result = $service->send('tenant-1', 'vendor-1', 'not-an-email', '', 'March 2026');

        self::assertFalse($result['ok']);
        self::assertSame('statement_mail_invalid_email', $result['message']);
        self::assertFalse($result['attached']);
        self::assertSame([
            ['name' => 'statement_mail_invalid_email_total', 'tags' => ['tenantId' => 'tenant-1', 'vendorId' => 'vendor-1']],
        ], $this->metrics->snapshot());
    }

    public function testSendRecordsMissingAttachmentButStillSendsMessage(): void
    {
        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(function (Email $message): bool {
                self::assertSame('Monthly Vendor Statement for March 2026', $message->getSubject());
                self::assertSame('vendor@example.com', $message->getTo()[0]->getAddress());
                self::assertCount(0, $message->getAttachments());

                return true;
            }));

        $service = new VendorStatementMailerService($this->mailer, $this->metrics);
        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '/tmp/missing-statement.pdf', 'March 2026');

        self::assertTrue($result['ok']);
        self::assertSame('sent', $result['message']);
        self::assertFalse($result['attached']);
        self::assertSame([
            ['name' => 'statement_mail_attachment_missing_total', 'tags' => ['tenantId' => 'tenant-1', 'vendorId' => 'vendor-1']],
            ['name' => 'statement_mail_sent_total', 'tags' => ['tenantId' => 'tenant-1', 'vendorId' => 'vendor-1']],
        ], $this->metrics->snapshot());
    }

    public function testSendAttachesReadablePdfAndMarksSuccess(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'statement-mail-');
        self::assertNotFalse($pdf);
        file_put_contents($pdf, 'pdf');

        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(function (Email $message): bool {
                self::assertCount(1, $message->getAttachments());

                return true;
            }));

        $service = new VendorStatementMailerService($this->mailer, $this->metrics);
        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', $pdf, 'March 2026');

        if (is_file($pdf)) {
            unlink($pdf);
        }

        self::assertTrue($result['ok']);
        self::assertSame('sent', $result['message']);
        self::assertTrue($result['attached']);
        self::assertSame([
            ['name' => 'statement_mail_sent_total', 'tags' => ['tenantId' => 'tenant-1', 'vendorId' => 'vendor-1']],
        ], $this->metrics->snapshot());
    }

    public function testSendCapturesTransportFailureAndEmitsFailureMetric(): void
    {
        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->willThrowException(new class('smtp down') extends \RuntimeException implements TransportExceptionInterface {
                public function getDebug(): string
                {
                    return 'smtp-debug';
                }
            });

        $service = new VendorStatementMailerService($this->mailer, $this->metrics);
        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '', 'March 2026');

        self::assertFalse($result['ok']);
        self::assertSame('statement_mail_send_failed', $result['message']);
        self::assertSame('smtp down', $result['errorMessage']);
        self::assertSame([
            ['name' => 'statement_mail_failed_total', 'tags' => ['tenantId' => 'tenant-1', 'vendorId' => 'vendor-1', 'errorClass' => TransportExceptionInterface::class]],
        ], array_map(
            static function (array $metric): array {
                if (str_contains($metric['tags']['errorClass'] ?? '', 'class@anonymous')) {
                    $metric['tags']['errorClass'] = TransportExceptionInterface::class;
                }

                return $metric;
            },
            $this->metrics->snapshot()
        ));
    }
}
