<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor\Interface\Ledger;

use App\DTO\Vendor\Ledger\DoubleEntryDTO;
use App\Entity\Vendor\Ledger\LedgerEntry;

interface DoubleEntryServiceInterface
{
    public function post(DoubleEntryDTO $dto): array;
}
