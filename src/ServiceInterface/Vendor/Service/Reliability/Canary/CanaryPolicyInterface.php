<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Reliability\Canary;

interface CanaryPolicyInterface
{

    public function allow(string $region, int $pct): bool;
}
