<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Entity\Payout;

interface PayoutItemInterface
{

    public function __construct(public string $id, public string $batchId, public string $referenceType, // order|refund|adjustment public string $referenceId, public float $amount, public string $currency);
}
