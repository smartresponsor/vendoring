<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor\Statement;

use App\DTO\Vendor\Statement\VendorStatementRequestDTO;

interface StatementExporterPDFInterface
{
    /** Returns absolute filepath to generated PDF */
    public function export(VendorStatementRequestDTO $dto, array $data, ?string $logoPath = null): string;
}
