<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Billing\Rating;

interface RatingEngineInterface
{

    public function rate(array $usage, string $plan): array;
}

