<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Ledger;

use App\Vendoring\DTO\Ledger\VendorLedgerDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntity;
use Doctrine\DBAL\Exception;
use Random\RandomException;

interface VendorLedgerServiceInterface
{
    /**
     * @throws Exception
     * @throws RandomException
     */
    public function record(VendorLedgerDTO $dto): VendorLedgerEntity;
}
