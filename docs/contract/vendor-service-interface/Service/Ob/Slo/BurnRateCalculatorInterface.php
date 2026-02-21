<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Ob\Slo;

interface BurnRateCalculatorInterface
{

    public function burn(float $errorRate, float $budget): float;
}

