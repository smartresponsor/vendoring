<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

/**
 * Application contract for vendor statement service operations.
 */
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
    public function build(\App\DTO\Statement\VendorStatementRequestDTO $dto): array;

    /**
     * Exports the requested artifact.
     */
    public function exportCsv(\App\DTO\Statement\VendorStatementRequestDTO $dto): string;
}
