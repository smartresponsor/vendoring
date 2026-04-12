<?php

declare(strict_types=1);

namespace App\Tests\Support\Statement;

use App\ServiceInterface\Statement\VendorStatementMailerServiceInterface;

final class FakeVendorStatementMailerService implements VendorStatementMailerServiceInterface
{
    /** @var list<array{tenantId:string,vendorId:string,email:string,pdfPath:string,periodLabel:string}> */
    private array $calls = [];

    public function __construct(private readonly bool $ok = true, private readonly string $message = 'sent') {}

    public function send(string $tenantId, string $vendorId, string $email, string $pdfPath, string $periodLabel): array
    {
        $this->calls[] = [
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
            'email' => $email,
            'pdfPath' => $pdfPath,
            'periodLabel' => $periodLabel,
        ];

        return [
            'ok' => $this->ok,
            'message' => $this->message,
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
            'email' => $email,
            'pdfPath' => $pdfPath,
            'periodLabel' => $periodLabel,
            'attached' => '' !== $pdfPath,
            'retryable' => false,
            'timeoutSeconds' => 0,
            'maxAttempts' => 0,
            'attemptCount' => 1,
            'failureMode' => 'hard',
            'circuitState' => 'closed',
        ];
    }

    /** @return list<array{tenantId:string,vendorId:string,email:string,pdfPath:string,periodLabel:string}> */
    public function calls(): array
    {
        return $this->calls;
    }
}
