<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\ServiceInterface\Observability\MetricCollectorInterface;
use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use App\ServiceInterface\Policy\OutboundOperationPolicyInterface;
use App\ServiceInterface\Reliability\OutboundCircuitBreakerInterface;
use App\ServiceInterface\Statement\VendorStatementMailerServiceInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Throwable;

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
        private MailerInterface                  $mailer,
        private MetricCollectorInterface         $metrics,
        private RuntimeLoggerInterface           $runtimeLogger,
        private OutboundOperationPolicyInterface $outboundPolicy,
        private OutboundCircuitBreakerInterface  $circuitBreaker,
    ) {}

    public function send(string $tenantId, string $vendorId, string $email, string $pdfPath, string $periodLabel): array
    {
        $policy = $this->outboundPolicy->forOperation('statement_mail_send');
        $scopeKey = $tenantId . ':' . $vendorId;

        $result = [
            'ok' => false,
            'message' => 'statement_mail_send_failed',
            'tenantId' => $tenantId,
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
                'tenantId' => $tenantId,
                'vendorId' => $vendorId,
            ]);
            $this->runtimeLogger->warning('vendor_statement_mail_rejected', [
                'tenant_id' => $tenantId,
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
                'tenantId' => $tenantId,
                'vendorId' => $vendorId,
            ]);
            $this->runtimeLogger->warning('vendor_statement_mail_short_circuited', [
                'tenant_id' => $tenantId,
                'vendor_id' => $vendorId,
                'email' => $email,
                'error_code' => 'statement_mail_circuit_open',
                'circuit_state' => $breaker['state'],
                'failure_count' => (string) $breaker['failureCount'],
            ]);

            $result['message'] = 'statement_mail_circuit_open';

            return $result;
        }

        $message = new Email()
            ->to($email)
            ->subject(sprintf('Monthly Vendor Statement for %s', $periodLabel))
            ->text(sprintf(
                "Hello,\nPlease find attached your statement for %s.\nTenant: %s, Vendor: %s",
                $periodLabel,
                $tenantId,
                $vendorId,
            ));

        $attached = '' !== $pdfPath && is_file($pdfPath) && is_readable($pdfPath);
        if ($attached) {
            $message->attachFromPath($pdfPath, 'statement.pdf', 'application/pdf');
        } elseif ('' !== $pdfPath) {
            $this->metrics->increment('statement_mail_attachment_missing_total', [
                'tenantId' => $tenantId,
                'vendorId' => $vendorId,
            ]);
            $this->runtimeLogger->warning('vendor_statement_mail_attachment_missing', [
                'tenant_id' => $tenantId,
                'vendor_id' => $vendorId,
                'pdf_path' => $pdfPath,
            ]);
        }

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            $updatedBreaker = $this->circuitBreaker->recordFailure(
                'statement_mail_send',
                $scopeKey,
                $policy['breakerThreshold'],
                $policy['cooldownSeconds'],
            );

            $this->metrics->increment('statement_mail_failed_total', [
                'tenantId' => $tenantId,
                'vendorId' => $vendorId,
                'errorClass' => $exception::class,
            ]);
            $this->runtimeLogger->error('vendor_statement_mail_failed', [
                'tenant_id' => $tenantId,
                'vendor_id' => $vendorId,
                'email' => $email,
                'error_class' => $exception::class,
                'error_code' => 'statement_mail_send_failed',
                'circuit_state' => $updatedBreaker['state'],
            ]);

            $result['attached'] = $attached;
            $result['attemptCount'] = 1;
            $result['circuitState'] = $updatedBreaker['state'];
            $result['errorClass'] = $exception::class;
            $result['errorMessage'] = '' !== trim($exception->getMessage())
                ? $exception->getMessage()
                : 'statement_mail_unknown_failure';

            return $result;
        } catch (Throwable $exception) {
            $updatedBreaker = $this->circuitBreaker->recordFailure(
                'statement_mail_send',
                $scopeKey,
                $policy['breakerThreshold'],
                $policy['cooldownSeconds'],
            );

            $this->metrics->increment('statement_mail_failed_total', [
                'tenantId' => $tenantId,
                'vendorId' => $vendorId,
                'errorClass' => $exception::class,
            ]);
            $this->runtimeLogger->error('vendor_statement_mail_failed', [
                'tenant_id' => $tenantId,
                'vendor_id' => $vendorId,
                'email' => $email,
                'error_class' => $exception::class,
                'error_code' => 'statement_mail_unexpected_failure',
                'circuit_state' => $updatedBreaker['state'],
            ]);

            $result['attached'] = $attached;
            $result['attemptCount'] = 1;
            $result['circuitState'] = $updatedBreaker['state'];
            $result['errorClass'] = $exception::class;
            $result['errorMessage'] = '' !== trim($exception->getMessage())
                ? $exception->getMessage()
                : 'statement_mail_unknown_failure';

            return $result;
        } catch (\JsonException $e) {
        } catch (\JsonException $e) {
        }

        $this->circuitBreaker->recordSuccess('statement_mail_send', $scopeKey);
        $this->metrics->increment('statement_mail_sent_total', [
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
        ]);
        $this->runtimeLogger->info('vendor_statement_mail_sent', [
            'tenant_id' => $tenantId,
            'vendor_id' => $vendorId,
            'email' => $email,
            'attached' => $attached,
        ]);

        return [
            'ok' => true,
            'message' => 'sent',
            'tenantId' => $tenantId,
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
