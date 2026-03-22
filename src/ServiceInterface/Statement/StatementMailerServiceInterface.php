<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

interface StatementMailerServiceInterface
{
    /** @return array{ok:bool, message:string, tenantId:string, vendorId:string, email:string, pdfPath:string, periodLabel:string, attached:bool} */
    public function send(string $tenantId, string $vendorId, string $email, string $pdfPath, string $periodLabel): array;
}
