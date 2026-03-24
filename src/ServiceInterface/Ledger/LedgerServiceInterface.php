<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Ledger;

use App\DTO\Ledger\LedgerEntryDTO;
use App\Entity\Vendor\Ledger\LedgerEntry;

interface LedgerServiceInterface
{
    public function record(LedgerEntryDTO $dto): LedgerEntry;
}
