<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO\Payout;

/**
 *
 */
interface CreatePayoutDTOInterface
{
    public function vendorId(): string;

    public function currency(): string;

    public function thresholdCents(): int;

    /** example: 0.05 for 5% */
    public function retentionFeePercent(): float;
}
