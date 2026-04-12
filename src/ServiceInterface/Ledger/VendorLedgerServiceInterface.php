<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Ledger;

use App\DTO\Ledger\LedgerEntryDTO;
use App\Entity\Ledger\LedgerEntry;
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
