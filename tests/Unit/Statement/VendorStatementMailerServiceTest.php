<?php

declare(strict_types=1);

namespace App\Tests\Unit\Statement;

use App\Observability\Service\CorrelationContext;
use App\Observability\Service\MetricEmitter;
use App\Observability\Service\RuntimeLogger;
use App\Service\Policy\OutboundOperationPolicy;
use App\Service\Reliability\FileOutboundCircuitBreaker;
use App\Service\Statement\VendorStatementMailerService;
use App\Tests\Support\Statement\FakeMailer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

final class VendorStatementMailerServiceTest extends TestCase
{
    public function testSendReturnsSuccessAndEmitsMetricWhenAttachmentExists(): void
    {
        $mailer = new FakeMailer();
        $metrics = new MetricEmitter();
        $service = $this->service($mailer, $metrics);
        $pdf = tempnam(sys_get_temp_dir(), 'statement-mail-');
        self::assertNotFalse($pdf);
        file_put_contents($pdf, 'pdf');

        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', $pdf, 'March 2026');

        self::assertTrue($result['ok']);
        self::assertSame('sent', $result['message']);
        self::assertTrue($result['attached']);
        self::assertSame('closed', $result['circuitState']);
        self::assertCount(1, $mailer->messages());
        self::assertSame('statement_mail_sent_total', $metrics->snapshot()[0]['name']);

        unlink($pdf);
    }

    public function testSendRejectsInvalidEmailWithoutCallingMailer(): void
    {
        $mailer = new FakeMailer();
        $metrics = new MetricEmitter();
        $service = $this->service($mailer, $metrics);

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
        $service = $this->service($mailer, $metrics);

        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '/tmp/missing.pdf', 'March 2026');

        self::assertFalse($result['ok']);
        self::assertSame('statement_mail_send_failed', $result['message']);
        self::assertFalse($result['attached']);
        self::assertArrayHasKey('errorMessage', $result);
        $errorMessage = $result['errorMessage'] ?? null;
        self::assertIsString($errorMessage);
        self::assertSame('mailer transport failed', $errorMessage);
        self::assertSame('closed', $result['circuitState']);
        self::assertSame('statement_mail_attachment_missing_total', $metrics->snapshot()[0]['name']);
        self::assertSame('statement_mail_failed_total', $metrics->snapshot()[1]['name']);
    }

    public function testSendShortCircuitsWhenCircuitBreakerIsOpen(): void
    {
        $mailer = new FakeMailer();
        $metrics = new MetricEmitter();
        $breaker = $this->breaker();
        $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 2, 60);

        $service = $this->service($mailer, $metrics, $breaker);
        $result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '', 'March 2026');

        self::assertFalse($result['ok']);
        self::assertSame('statement_mail_circuit_open', $result['message']);
        self::assertSame('open', $result['circuitState']);
        self::assertCount(0, $mailer->messages());
        self::assertSame('statement_mail_circuit_open_total', $metrics->snapshot()[0]['name']);
    }

    public function testSendDoesNotSwallowNonTransportExceptions(): void
    {
        $mailer = new class implements MailerInterface {
            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                throw new \LogicException('unexpected mailer state');
            }
        };

        $metrics = new MetricEmitter();
        $service = $this->service($mailer, $metrics);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('unexpected mailer state');

        $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '/tmp/missing.pdf', 'March 2026');
    }

    private function service(MailerInterface $mailer, MetricEmitter $metrics, ?FileOutboundCircuitBreaker $breaker = null): VendorStatementMailerService
    {
        return new VendorStatementMailerService(
            $mailer,
            $metrics,
            $this->runtimeLogger(),
            new OutboundOperationPolicy(),
            $breaker ?? $this->breaker(),
        );
    }

    private function runtimeLogger(): RuntimeLogger
    {
        return new RuntimeLogger(new CorrelationContext(), new RequestStack());
    }

    private function breaker(): FileOutboundCircuitBreaker
    {
        return new FileOutboundCircuitBreaker(sys_get_temp_dir() . '/vendoring-breaker-mailer-' . bin2hex(random_bytes(4)));
    }
}
