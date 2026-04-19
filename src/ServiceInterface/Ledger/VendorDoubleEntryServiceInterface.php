<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ledger;

use App\Vendoring\DTO\Ledger\DoubleEntryDTO;
use App\Vendoring\Entity\Ledger\LedgerEntry;

interface VendorDoubleEntryServiceInterface
{
    /** @return array{0: LedgerEntry} */
    public function post(DoubleEntryDTO $dto): array;
}
