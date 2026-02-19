<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Metering;

interface DedupStoreInterface
{

    public function hit(string $key, int $ttlSec = 86400): bool;
}
