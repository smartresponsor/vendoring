<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use App\Vendoring\PolicyInterface\VendorOutboundOperationPolicyInterface;
use App\Vendoring\ServiceInterface\Observability\VendorMetricCollectorServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorRuntimeLoggerServiceInterface;
use App\Vendoring\ServiceInterface\Reliability\VendorOutboundCircuitBreakerServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementMailerServiceInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Write-side outbound mail transport for vendor statements.
 *
 * The service validates destination input, attaches the statement when present,
 * applies outbound runtime policy, consults the circuit breaker, and attempts one
 * transport send through Symfony Mailer.
 */
final readonly class VendorStatementMailerService implements VendorStatementMailerServiceInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private VendorMetricCollectorServiceInterface $metrics,
        private VendorRuntimeLoggerServiceInterface $runtimeLogger,
        private VendorOutboundOperationPolicyInterface $outboundPolicy,
        private VendorOutboundCircuitBreakerServiceInterface $circuitBreaker,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function send(string $vendorId, string $email, string $pdfPath, string $periodLabel): array
    {
        $policy = $this->outboundPolicy->forOperation('statement_mail_send');
        $scopeKey = $vendorId;

        $result = [
            'ok' => false,
            'message' => 'statement_mail_send_failed',
            'vendorId' => $vendorId,
            'email' => $email,
            'pdfPath' => $pdfPath,
            'periodLabel' => $periodLabel,
            'attached' => false,
            'retryable' => $policy['retryable'],
            'timeoutSeconds' => $policy['timeoutSeconds'],
            'maxAttempts' => $policy['maxAttempts'],
            'attemptCount' => 0,
            'failureMode' => $policy['failureMode'],
            'circuitState' => 'closed',
        ];

        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->metrics->increment('statement_mail_invalid_email_total', [
                'vendorId' => $vendorId,
            ]);
            $this->runtimeLogger->warning('vendor_statement_mail_rejected', [
                'vendor_id' => $vendorId,
                'email' => $email,
                'error_code' => 'statement_mail_invalid_email',
            ]);

            $result['message'] = 'statement_mail_invalid_email';

            return $result;
        }

        $breaker = $this->circuitBreaker->currentState(
            'statement_mail_send',
            $scopeKey,
            $policy['breakerThreshold'],
            $policy['cooldownSeconds'],
        );
        $result['circuitState'] = $breaker['state'];

        if (true !== $breaker['allowRequest']) {
            $this->metrics->increment('statement_mail_circuit_open_total', [
                'vendorId' => $vendorId,
            ]);
            $this->runtimeLogger->warning('vendor_statement_mail_short_circuited', [
                'vendor_id' => $vendorId,
                'email' => $email,
                'error_code' => 'statement_mail_circuit_open',
                'circuit_state' => $breaker['state'],
                'failure_count' => (string) $breaker['failureCount'],
            ]);

            $result['message'] = 'statement_mail_circuit_open';

            return $result;
        }

        $message = new Email();
        $message
            ->to($email)
            ->subject(sprintf('Monthly Vendor Statement for %s', $periodLabel))
            ->text(sprintf(
                "Hello,\nPlease find attached your statement for %s.\nVendor: %s",
                $periodLabel,
                $vendorId,
            ));

        $attached = '' !== $pdfPath && is_file($pdfPath) && is_readable($pdfPath);
        if ($attached) {
            $message->attachFromPath($pdfPath, 'statement.pdf', 'application/pdf');
        } elseif ('' !== $pdfPath) {
            $this->metrics->increment('statement_mail_attachment_missing_total', [
                'vendorId' => $vendorId,
            ]);
            $this->runtimeLogger->warning('vendor_statement_mail_attachment_missing', [
                'vendor_id' => $vendorId,
                'pdf_path' => $pdfPath,
            ]);
        }

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $transportException) {
            $updatedBreaker = $this->circuitBreaker->recordFailure(
                'statement_mail_send',
                $scopeKey,
                $policy['breakerThreshold'],
                $policy['cooldownSeconds'],
            );

            $this->metrics->increment('statement_mail_failed_total', [
                'vendorId' => $vendorId,
                'errorClass' => $transportException::class,
            ]);
            $this->runtimeLogger->error('vendor_statement_mail_failed', [
                'vendor_id' => $vendorId,
                'email' => $email,
                'error_class' => $transportException::class,
                'error_code' => 'statement_mail_send_failed',
                'circuit_state' => $updatedBreaker['state'],
            ]);

            $result['attached'] = $attached;
            $result['attemptCount'] = 1;
            $result['circuitState'] = $updatedBreaker['state'];
            $result['errorClass'] = $transportException::class;
            $result['errorMessage'] = '' !== trim($transportException->getMessage())
                ? $transportException->getMessage()
                : 'statement_mail_unknown_failure';

            return $result;
        }

        $this->circuitBreaker->recordSuccess('statement_mail_send', $scopeKey);
        $this->metrics->increment('statement_mail_sent_total', [
            'vendorId' => $vendorId,
        ]);
        $this->runtimeLogger->info('vendor_statement_mail_sent', [
            'vendor_id' => $vendorId,
            'email' => $email,
            'attached' => $attached,
        ]);

        return [
            'ok' => true,
            'message' => 'sent',
            'vendorId' => $vendorId,
            'email' => $email,
            'pdfPath' => $pdfPath,
            'periodLabel' => $periodLabel,
            'attached' => $attached,
            'retryable' => $policy['retryable'],
            'timeoutSeconds' => $policy['timeoutSeconds'],
            'maxAttempts' => $policy['maxAttempts'],
            'attemptCount' => 1,
            'failureMode' => $policy['failureMode'],
            'circuitState' => 'closed',
        ];
    }
}
