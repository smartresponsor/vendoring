<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ledger;

use App\Vendoring\DTO\Ledger\VendorDoubleEntryDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntity;

interface VendorDoubleEntryServiceInterface
{
    /** @return array{0: VendorLedgerEntity} */
    public function post(VendorDoubleEntryDTO $dto): array;
}
