<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Ledger;

use App\DTO\Ledger\LedgerEntryDTO;
use App\Entity\Ledger\LedgerEntry;

/**
 * Application contract for vendor ledger service operations.
 */
interface VendorLedgerServiceInterface
{
    /**
     * Records the requested runtime state change.
     */
    public function record(LedgerEntryDTO $dto): LedgerEntry;
}
