<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ledger;

use App\Vendoring\DTO\Ledger\LedgerEntryDTO;
use App\Vendoring\Entity\Ledger\LedgerEntry;
use Doctrine\DBAL\Exception;
use Random\RandomException;

interface VendorLedgerServiceInterface
{
    /**
     * @throws Exception
     * @throws RandomException
     */
    public function record(LedgerEntryDTO $dto): LedgerEntry;
}
