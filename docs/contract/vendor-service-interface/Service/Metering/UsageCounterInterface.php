<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Metering;

interface UsageCounterInterface
{

    public function incr(string $rollupKey, int $n = 1): void;
}

