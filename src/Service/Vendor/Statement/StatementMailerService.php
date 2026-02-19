<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Statement;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\ServiceInterface\Vendor\Statement\StatementMailerServiceInterface;

final class StatementMailerService implements StatementMailerServiceInterface
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function send(string $tenantId, string $vendorId, string $email, string $pdfPath, string $periodLabel): array
    {
        $subject = sprintf('Monthly Vendor Statement for %s', $periodLabel);
        $body = sprintf('Hello,\nPlease find attached your statement for %s.\nTenant: %s, Vendor: %s', $periodLabel, $tenantId, $vendorId);
        $message = (new Email())->to($email)->subject($subject)->text($body);
        if (is_file($pdfPath)) {
            $message->attachFromPath($pdfPath, 'statement.pdf', 'application/pdf');
        }
        try {
            $this->mailer->send($message);
            return ['ok' => true, 'message' => 'sent'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
