<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use Doctrine\DBAL\Exception;

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
     * @throws Exception
     */
    public function build(VendorStatementRequestDTO $dto): array;

    /** @throws Exception */
    public function exportCsv(VendorStatementRequestDTO $dto): string;
}
