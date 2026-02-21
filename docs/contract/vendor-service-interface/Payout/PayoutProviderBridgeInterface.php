<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Payout;

interface PayoutProviderBridgeInterface
{

    public function transfer(string $tenantId, string $vendorId, string $provider, string $accountRef, float $amount, string $currency): array;
}

