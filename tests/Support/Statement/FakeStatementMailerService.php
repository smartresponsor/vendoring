<?php

declare(strict_types=1);

namespace App\Tests\Support\Statement;

use App\ServiceInterface\Statement\StatementMailerServiceInterface;

final class FakeStatementMailerService implements StatementMailerServiceInterface
{
    /** @var list<array{tenantId:string,vendorId:string,email:string,pdfPath:string,periodLabel:string}> */
    private array $calls = [];

    public function __construct(private readonly bool $ok = true, private readonly string $message = 'sent')
    {
    }

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
        ];
    }

    /** @return list<array{tenantId:string,vendorId:string,email:string,pdfPath:string,periodLabel:string}> */
    public function calls(): array
    {
        return $this->calls;
    }
}
