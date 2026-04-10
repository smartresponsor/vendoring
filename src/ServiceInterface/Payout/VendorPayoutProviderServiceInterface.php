<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Payout;

use App\DTO\Payout\VendorPayoutTransferDTO;

interface VendorPayoutProviderServiceInterface
{
    /** @return array<string, mixed> */
    public function transfer(VendorPayoutTransferDTO $transfer): array;
}
