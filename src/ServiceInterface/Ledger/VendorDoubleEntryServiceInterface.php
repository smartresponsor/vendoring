<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ledger;

use App\DTO\Ledger\DoubleEntryDTO;

interface VendorDoubleEntryServiceInterface
{
    public function post(DoubleEntryDTO $dto): array;
}
