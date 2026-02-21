<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Billing\Rating;

interface ProrationInterface
{

    public function fraction(string $startIso, string $endIso, string $periodStartIso, string $periodEndIso): float;
}

