<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ProviderInterface\Payout;

use App\Vendoring\DTO\Payout\VendorPayoutTransferDTO;
use Random\RandomException;

interface VendorPayoutProviderInterface
{
    /**
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    public function transfer(VendorPayoutTransferDTO $transfer): array;
}
