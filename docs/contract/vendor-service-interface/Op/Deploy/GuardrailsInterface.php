<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Op\Deploy;

interface GuardrailsInterface
{

    public function allow(float $errPct, int $p95): bool;
}

