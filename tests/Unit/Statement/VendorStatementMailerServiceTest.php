<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Statement;

use App\Observability\Service\MetricEmitter;
use App\Service\Statement\VendorStatementMailerService;
use App\Tests\Support\Statement\FakeMailer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

final class VendorStatementMailerServiceTest extends TestCase
{
    public function testSendReturnsSuccessAndEmitsMetricWhenAttachmentExists(): void
    {
        $mailer = new FakeMailer();
        $metrics = new MetricEmitter();
        $service = new VendorStatementMailerService($mailer, $metrics);
        $pdf = tempnam(sys_get_temp_dir(), 'statement-mail-');
        self::assertNotFalse($pdf);
        file_put_contents($pdf, 'pdf');

        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', $pdf, 'March 2026');

        self::assertTrue($result['ok']);
        self::assertSame('sent', $result['message']);
        self::assertTrue($result['attached']);
        self::assertCount(1, $mailer->messages());
        self::assertSame('statement_mail_sent_total', $metrics->snapshot()[0]['name']);

        unlink($pdf);
    }

    public function testSendRejectsInvalidEmailWithoutCallingMailer(): void
    {
        $mailer = new FakeMailer();
        $metrics = new MetricEmitter();
        $service = new VendorStatementMailerService($mailer, $metrics);

        $result = $service->send('tenant-1', 'vendor-1', 'not-an-email', '/tmp/missing.pdf', 'March 2026');

        self::assertFalse($result['ok']);
        self::assertSame('statement_mail_invalid_email', $result['message']);
        self::assertCount(0, $mailer->messages());
        self::assertSame('statement_mail_invalid_email_total', $metrics->snapshot()[0]['name']);
    }

    public function testSendReturnsFailureAndEmitsMetricsWhenMailerThrows(): void
    {
        $mailer = new FakeMailer(true);
        $metrics = new MetricEmitter();
        $service = new VendorStatementMailerService($mailer, $metrics);

        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '/tmp/missing.pdf', 'March 2026');

        self::assertFalse($result['ok']);
        self::assertSame('statement_mail_send_failed', $result['message']);
        self::assertFalse($result['attached']);
        self::assertSame('mailer transport failed', $result['errorMessage']);
        self::assertSame('statement_mail_attachment_missing_total', $metrics->snapshot()[0]['name']);
        self::assertSame('statement_mail_failed_total', $metrics->snapshot()[1]['name']);
    }

    public function testSendDoesNotSwallowNonTransportExceptions(): void
    {
        $mailer = new class () implements MailerInterface {
            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                throw new \LogicException('unexpected mailer state');
            }
        };

        $metrics = new MetricEmitter();
        $service = new VendorStatementMailerService($mailer, $metrics);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('unexpected mailer state');

        $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '/tmp/missing.pdf', 'March 2026');
    }
}
