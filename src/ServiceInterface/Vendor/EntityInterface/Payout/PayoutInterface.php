<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\EntityInterface\Payout;

interface PayoutInterface
{

    public function __construct(public string $id, public string $vendorId, public string $currency, public int $grossCents, public int $feeCents, public int $netCents, public string $status, // pending|processed|failed public string $createdAt, public ?string $processedAt = null, public array $meta = []);
}
