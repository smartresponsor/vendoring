<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Billing\Money;

interface CurrencyInterface
{

    public function minorUnits(string $code): int;

    public function isValid(string $code): bool;
}

