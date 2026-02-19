<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Reliability\Resilience;

interface CircuitBreakerInterface
{

    public function allow(): bool;

    public function record(bool $ok): void;
}
