<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Statement;

use App\Observability\Service\MetricEmitter;
use App\ServiceInterface\Statement\StatementMailerServiceInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class StatementMailerService implements StatementMailerServiceInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly MetricEmitter $metrics,
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
        }

        try {
            $this->mailer->send($message);
        } catch (\Throwable $exception) {
            $this->metrics->increment('statement_mail_failed_total', [
                'tenantId' => $tenantId,
                'vendorId' => $vendorId,
                'errorClass' => $exception::class,
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
