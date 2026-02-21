<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Pricing;

interface EntitlementsInterface
{

    public function allowed(string $plan, string $feature): bool;
}

