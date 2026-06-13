<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;

interface VendorStatementExporterPdfServiceInterface
{
    /** Returns absolute filepath to the generated PDF statement artifact.
     * @param array{tenantId:string, vendorId:string, from:string, to:string, currency:string, opening:float, earnings:float, refunds:float, fees:float, closing:float, items:list<array{type:string, amount:float, currency:string}>} $data
     */
    public function export(VendorStatementRequestDTO $dto, array $data, ?string $logoPath = null): string;
}
