<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;

interface VendorStatementServiceInterface
{
    /**
     * @return array{
     *   tenantId:string,
     *   vendorId:string,
     *   from:string,
     *   to:string,
     *   currency:string,
     *   opening:float,
     *   earnings:float,
     *   refunds:float,
     *   fees:float,
     *   closing:float,
     *   items:list<array{type:string, amount:float, currency:string}>
     * }
     */
    public function build(VendorStatementRequestDTO $dto): array;

    public function exportCsv(VendorStatementRequestDTO $dto): string;
}
