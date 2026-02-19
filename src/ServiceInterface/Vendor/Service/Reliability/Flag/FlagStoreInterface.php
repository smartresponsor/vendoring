<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Reliability\Flag;

interface FlagStoreInterface
{

    public function isOn(string $flag, string $vendorId, int $pct = 5): bool;
}
