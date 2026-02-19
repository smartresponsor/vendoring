<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Finop\Cost;

interface AnomalyDetectorInterface
{

    public function isAnomalous(float $spend, float $expected): bool;
}
