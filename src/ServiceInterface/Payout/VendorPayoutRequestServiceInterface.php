<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Payout;

use App\DTO\Payout\CreatePayoutDTO;
use App\Entity\Payout\Payout;

interface VendorPayoutRequestServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function toCreateDto(array $payload): CreatePayoutDTO;

    /**
     * @return array<string, mixed>
     */
    public function normalizePayout(Payout $payout): array;
}
