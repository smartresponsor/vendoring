<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Statement;

use App\ServiceInterface\Observability\MetricCollectorInterface;
use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use App\ServiceInterface\Statement\VendorStatementMailerServiceInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class VendorStatementMailerService implements VendorStatementMailerServiceInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly MetricCollectorInterface $metrics,
        private readonly RuntimeLoggerInterface $runtimeLogger,
    ) {
    }

    public function send(string $tenantId, string $vendorId, string $email, string $pdfPath, string $periodLabel): array
    {
        $result = [
            'ok' => false,
            'message' => 'statement_mail_send_failed',
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
            'email' => $email,
            'pdfPath' => $pdfPath,
            'periodLabel' => $periodLabel,
            'attached' => false,
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

        $message = (new Email())
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
            ]);

            $result['attached'] = $attached;
            $result['errorClass'] = $exception::class;
            $result['errorMessage'] = '' !== trim($exception->getMessage())
                ? $exception->getMessage()
                : 'statement_mail_unknown_failure';

            return $result;
        }

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
        ];
    }
}
