<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;

interface StatementExporterPDFInterface
{
    /** Returns absolute filepath to generated PDF */
    public function export(VendorStatementRequestDTO $dto, array $data, ?string $logoPath = null): string;
}
