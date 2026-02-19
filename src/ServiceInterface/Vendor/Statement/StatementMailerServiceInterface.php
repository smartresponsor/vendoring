<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor\Statement;
interface StatementMailerServiceInterface
{
    /** @return array{ok:bool, message:string} */
    public function send(string $tenantId, string $vendorId, string $email, string $pdfPath, string $periodLabel): array;
}
