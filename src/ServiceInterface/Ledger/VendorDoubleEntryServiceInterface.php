<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ledger;

use App\DTO\Ledger\DoubleEntryDTO;
use App\Entity\Ledger\LedgerEntry;

/**
 * Application contract for vendor double entry service operations.
 */
interface VendorDoubleEntryServiceInterface
{
    /** @return array{0: LedgerEntry} */
    public function post(DoubleEntryDTO $dto): array;
}
