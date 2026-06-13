<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Payout;

use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use App\Vendoring\Entity\Vendor\VendorPayoutEntity;

interface VendorPayoutRequestServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function toCreateDto(array $payload): VendorCreatePayoutDTO;

    /**
     * @return array<string, mixed>
     */
    public function normalizePayout(VendorPayoutEntity $payout): array;
}
